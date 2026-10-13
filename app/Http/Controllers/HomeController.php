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
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // ============ FINANCIEROS DEL MES ============
        $ahora     = Carbon::now();
        $anio      = $ahora->year;
        $mes       = $ahora->month;
        $empresaId = 1;

        // --- 1) Ingresos y descuentos del mes (base: movimientos activos) ---
        $movMesBase = MovimientoNomina::where('empresa_id', $empresaId)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->where('estado', 'activo');

        // Ingresos = sueldo + extra (todos los es_ingreso = true)
        $totalIngresosMes = (int) (clone $movMesBase)
            ->where('es_ingreso', true)
            ->sum('monto');

        // Descuentos = vale + ausencia + llegada_tardia + otros (es_ingreso = false)
        $totalDescuentosMes = (int) (clone $movMesBase)
            ->where('es_ingreso', false)
            ->sum('monto');

        // --- 2) Base IPS: solo sueldo + extra ---
        $baseIps = (int) (clone $movMesBase)
            ->where('es_ingreso', true)
            ->whereIn('tipo_movimiento', ['sueldo', 'extra'])
            ->sum('monto');

        // --- 3) IPS según parámetro del período ---
        $parametro     = ParametroIps::paraPeriodo($empresaId, $anio, $mes);
        $porcentajeIps = (float) $parametro->aporte_empleado;   // 9.00 por defecto
        $totalIpsMes   = (int) round($baseIps * ($porcentajeIps / 100));

        // --- 4) Neto a pagar del mes ---
        $totalNetoMes = $totalIngresosMes - $totalDescuentosMes - $totalIpsMes;

        // --- 5) % de descuento sobre bruto ---
        $baseDevengado  = $totalNetoMes + $totalDescuentosMes;
        $porcentajeDesc = $baseDevengado > 0
            ? round(($totalDescuentosMes / $baseDevengado) * 100, 1)
            : 0;

        // --- 6) Movimientos específicos para cards ---
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

        // ============ PENDIENTES DE PAGO ============
        // Excluye anuladas. Pendiente = sin fecha_pago y estado != anulado.
        $pendientesQuery = LiquidacionSalarial::where('empresa_id', $empresaId)
            ->where('periodo_anio', $anio)
            ->where('periodo_mes', $mes)
            ->where('estado', '!=', 'anulado')
            ->whereNull('fecha_pago');

        $totalPendienteCount = (clone $pendientesQuery)->count();
        $totalPendienteMonto = (int) (clone $pendientesQuery)->sum('total_neto');

        // ============ COSTO PROMEDIO ============
        $costoPromedio = $totalEmpleadosActivos > 0
            ? (int) round($totalNetoMes / $totalEmpleadosActivos)
            : 0;

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

            $baseIps = (int) MovimientoNomina::where('empresa_id', $empresaId)
                ->where('anio', $f->year)->where('mes', $f->month)
                ->where('estado', 'activo')->where('es_ingreso', true)
                ->whereIn('tipo_movimiento', ['sueldo', 'extra'])
                ->sum('monto');

            $param = ParametroIps::paraPeriodo($empresaId, $f->year, $f->month);
            $pct   = (float) $param->aporte_empleado;
            $ips   = (int) round($baseIps * ($pct / 100));

            $evolucionLabels[] = ucfirst($f->translatedFormat('M y'));
            $evolucionSeries[] = $ing - $des - $ips;
        }

        // ============ COMPARATIVA INGRESOS VS DESCUENTOS ============
        $compLabels     = [];
        $compIngresos   = [];
        $compDescuentos = [];

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
            'totalNetoMes',
            'totalIngresosMes',
            'totalDescuentosMes',
            'totalIpsMes',
            'porcentajeDesc',
            'totalPendienteCount',
            'totalPendienteMonto',
            'extrasMes',
            'valesMes',
            'inasistenciasMes',
            'costoPromedio',
            'evolucionLabels',
            'evolucionSeries',
            'compLabels',
            'compIngresos',
            'compDescuentos'
        ));
    }
}