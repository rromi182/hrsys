<?php
// app/Http/Controllers/HomeController.php
namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Departamento;
use App\Models\Cargo;
use App\Models\LiquidacionSalarial;
use App\Models\MovimientoNomina;
use App\Models\ParametroIps;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // ============ KPIs básicos ============
        $totalEmpleadosActivos = Empleado::where('estado', 'activo')->count();
        $totalEmpleados        = Empleado::count();
        $totalDepartamentos    = Departamento::count();
        $totalCargos           = Cargo::count();
        $salarioPromedio       = Empleado::where('estado', 'activo')->avg('salario_base') ?? 0;

        $nuevosIngresos = Empleado::whereMonth('fecha_ingreso', now()->month)
            ->where('estado', 'activo') 
            ->whereYear('fecha_ingreso', now()->year)
            ->count();

        $porEstado = Empleado::select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        $porDepartamento = Empleado::where('empleados.estado', 'activo')
            ->join('departamentos', 'departamentos.id', '=', 'empleados.departamento_id')
            ->select('departamentos.nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('departamentos.nombre')
            ->orderByDesc('total')
            ->limit(6)
            ->pluck('total', 'nombre')
            ->toArray();

        $empleados = Empleado::with(['cargo', 'departamento'])
         ->where('estado', 'activo')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // ============ FINANCIEROS DEL MES ============
        $ahora     = Carbon::now();
        $anio      = $ahora->year;
        $mes       = $ahora->month;
        $empresaId = 1;

        // Base: movimientos activos del mes
        $movMesBase = MovimientoNomina::where('empresa_id', $empresaId)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->where('estado', 'activo');

        // Devengado bruto = sueldo + extra (todos es_ingreso = true)
        $totalDevengado = (int) (clone $movMesBase)
            ->where('es_ingreso', true)
            ->sum('monto');

        // Otros descuentos al trabajador (sin IPS)
        $otrosDescuentos = (int) (clone $movMesBase)
            ->where('es_ingreso', false)
            ->sum('monto');

        // Base IPS = sueldo + extra SOLO de empleados afiliados
        $baseIps = (int) (clone $movMesBase)
            ->whereHas('empleado', function($q) {
                $q->where('afiliado_ips', true);
            })
            ->where('es_ingreso', true)
            ->whereIn('tipo_movimiento', ['sueldo', 'extra'])
            ->sum('monto');

        // Parámetros IPS del período
        $parametroIps     = ParametroIps::paraPeriodo($empresaId, $anio, $mes);
        $pctIpsTrabajador = (float) $parametroIps->aporte_empleado;   // 9.00
        $pctIpsEmpleador  = (float) $parametroIps->aporte_empleador;  // 16.50

        $ipsTrabajador = (int) round($baseIps * ($pctIpsTrabajador / 100));
        $ipsEmpleador  = (int) round($baseIps * ($pctIpsEmpleador / 100));

        // Neto a pagar = devengado − IPS trabajador − otros descuentos
        $totalNetoMes = $totalDevengado - $ipsTrabajador - $otrosDescuentos;

        // Costo total para la empresa = devengado + IPS empleador
        $costoTotalEmpresa = $totalDevengado + $ipsEmpleador;

        // Movimientos específicos para cards
        $extrasMes = (int) (clone $movMesBase)
            ->where('es_ingreso', true)
            ->where('tipo_movimiento', 'extra')
            ->sum('monto');

        $valesMes = (int) (clone $movMesBase)
            ->where('tipo_movimiento', 'vale')
            ->sum('monto');

        $inasistenciasMes = (int) (clone $movMesBase)
            ->whereIn('tipo_movimiento', ['ausencia', 'llegada_tardia'])
            ->sum('monto');

        // Costo promedio por colaborador (sobre el neto)
        $costoPromedio = $totalEmpleadosActivos > 0
            ? (int) round($totalNetoMes / $totalEmpleadosActivos)
            : 0;

        // ============ PENDIENTES DE PAGO ============
        $pendientesQuery = LiquidacionSalarial::where('empresa_id', $empresaId)
            ->where('periodo_anio', $anio)
            ->where('periodo_mes', $mes)
            ->where('estado', '!=', 'anulado')
            ->whereNull('fecha_pago');

        $totalPendienteCount = (clone $pendientesQuery)->count();
        $totalPendienteMonto = (int) (clone $pendientesQuery)->sum('total_neto');

        // ============ EVOLUCIÓN 6 MESES (neto calculado) ============
        $evolucionLabels = [];
        $evolucionSeries = [];

                foreach (range(5, 0) as $i) {
            $f = $ahora->copy()->subMonths($i);

            $ing = (int) MovimientoNomina::where('empresa_id', $empresaId)
                ->where('anio', $f->year)->where('mes', $f->month)
                ->where('estado', 'activo')->where('es_ingreso', true)
                ->sum('monto');

            $des = (int) MovimientoNomina::where('empresa_id', $empresaId)
                ->where('anio', $f->year)->where('mes', $f->month)
                ->where('estado', 'activo')->where('es_ingreso', false)
                ->sum('monto');

            // AGREGADO: whereHas empleado afiliado_ips
            $baseIpsMes = (int) MovimientoNomina::where('empresa_id', $empresaId)
                ->where('anio', $f->year)->where('mes', $f->month)
                ->where('estado', 'activo')->where('es_ingreso', true)
                ->whereHas('empleado', function($q) {
                    $q->where('afiliado_ips', true);
                })
                ->whereIn('tipo_movimiento', ['sueldo', 'extra'])
                ->sum('monto');

            $param = ParametroIps::paraPeriodo($empresaId, $f->year, $f->month);
            $ips   = (int) round($baseIpsMes * ((float) $param->aporte_empleado / 100));

            $evolucionLabels[] = ucfirst($f->translatedFormat('M y'));
            $evolucionSeries[] = $ing - $des - $ips;
        }

        // ============ COMPARATIVA DEVENGADO VS DESCUENTOS ============
        $compLabels     = [];
        $compIngresos   = [];   // acá va el DEVENGADO
        $compDescuentos = [];   // acá van los descuentos

        foreach (range(5, 0) as $i) {
            $f = $ahora->copy()->subMonths($i);

            $compLabels[] = ucfirst($f->translatedFormat('M y'));

            $compIngresos[] = (int) MovimientoNomina::where('empresa_id', $empresaId)
                ->where('anio', $f->year)->where('mes', $f->month)
                ->where('estado', 'activo')->where('es_ingreso', true)
                ->sum('monto');

            $compDescuentos[] = (int) MovimientoNomina::where('empresa_id', $empresaId)
                ->where('anio', $f->year)->where('mes', $f->month)
                ->where('estado', 'activo')->where('es_ingreso', false)
                ->sum('monto');
        }

        return view('dashboard.home', compact(
            'totalEmpleadosActivos',
            'totalEmpleados',
            'totalDepartamentos',
            'totalCargos',
            'salarioPromedio',
            'nuevosIngresos',
            'porEstado',
            'porDepartamento',
            'empleados',
            // Financieros nuevos
            'totalDevengado',
            'otrosDescuentos',
            'baseIps',
            'pctIpsTrabajador',
            'pctIpsEmpleador',
            'ipsTrabajador',
            'ipsEmpleador',
            'totalNetoMes',
            'costoTotalEmpresa',
            'totalPendienteCount',
            'totalPendienteMonto',
            'extrasMes',
            'valesMes',
            'inasistenciasMes',
            'costoPromedio',
            // Gráficos
            'evolucionLabels',
            'evolucionSeries',
            'compLabels',
            'compIngresos',
            'compDescuentos'
        ));
    }
}