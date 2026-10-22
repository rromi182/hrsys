<?php
// app/Http/Controllers/HomeController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
    public function index(Request $request)
    {
        // ============ FILTROS DE PERÍODO ============
        // Por defecto mostramos el MES ACTUAL del sistema
        $mesDefault = Carbon::now()->month;
        $anioDefault = Carbon::now()->year;

        // Si estamos en Enero, el mes anterior es Diciembre del año pasado
        if (Carbon::now()->month === 1) {
            $mesDefault = 12;
            $anioDefault = Carbon::now()->year - 1;
        }

        $anio = (int) $request->get('anio', $anioDefault);
        $mes  = (int) $request->get('mes', $mesDefault);
        $empresaId = 1;

        // Objeto Carbon del período para título
        $fechaPeriodo = Carbon::createFromDate($anio, $mes, 1);
        $nombreMes = ucfirst($fechaPeriodo->translatedFormat('F Y'));

        // ============ KPIs BÁSICOS ============
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

        // ============ FINANCIEROS DEL PERÍODO SELECCIONADO ============
        // Base: movimientos activos del mes y año seleccionados
        $movMesBase = MovimientoNomina::where('empresa_id', $empresaId)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->where('estado', 'activo');

        // Devengado bruto = sueldo + extra
        $totalDevengado = (int) (clone $movMesBase)
            ->where('es_ingreso', true)
            ->sum('monto');

        // Otros descuentos (sin IPS)
        $otrosDescuentos = (int) (clone $movMesBase)
            ->where('es_ingreso', false)
            ->sum('monto');

        // Base IPS = sueldo + extra SOLO de empleados afiliados
        $baseIps = (int) (clone $movMesBase)
            ->whereHas('empleado', function ($q) {
                $q->where('afiliado_ips', true);
            })
            ->where('es_ingreso', true)
            ->whereIn('tipo_movimiento', ['sueldo', 'extra'])
            ->sum('monto');

        // Parámetros IPS del período
        $parametroIps     = ParametroIps::paraPeriodo($empresaId, $anio, $mes);
        $pctIpsTrabajador = (float) $parametroIps->aporte_empleado;
        $pctIpsEmpleador  = (float) $parametroIps->aporte_empleador;

        $ipsTrabajador = (int) round($baseIps * ($pctIpsTrabajador / 100));
        $ipsEmpleador  = (int) round($baseIps * ($pctIpsEmpleador / 100));

        // Neto a pagar
        $totalNetoMes = $totalDevengado - $ipsTrabajador - $otrosDescuentos;

        // Costo total para la empresa
        $costoTotalEmpresa = $totalDevengado + $ipsEmpleador;

        // Movimientos específicos
        $extrasMes = (int) (clone $movMesBase)->where('es_ingreso', true)->where('tipo_movimiento', 'extra')->sum('monto');
        $valesMes = (int) (clone $movMesBase)->where('tipo_movimiento', 'vale')->sum('monto');
        $inasistenciasMes = (int) (clone $movMesBase)->whereIn('tipo_movimiento', ['ausencia', 'llegada_tardia'])->sum('monto');

        // Costo promedio
        $costoPromedio = $totalEmpleadosActivos > 0 ? (int) round($totalNetoMes / $totalEmpleadosActivos) : 0;

        // ============ LIQUIDACIONES PENDIENTES ============
        // 1. Empleados que ya tienen una liquidación generada este mes
        $empleadosLiquidados = LiquidacionSalarial::where('empresa_id', $empresaId)
            ->where('periodo_anio', $anio)
            ->where('periodo_mes', $mes)
            ->where('estado', '!=', 'anulado')
            ->pluck('empleado_id')
            ->toArray();

        // 2. Empleados que tienen movimientos en este mes (trabajaron)
        $empleadosConMovimientos = MovimientoNomina::where('empresa_id', $empresaId)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->where('estado', 'activo')
            ->pluck('empleado_id')
            ->unique()
            ->toArray();

        // 3. La diferencia son los que trabajaron pero NO tienen liquidación
        $pendientesDeLiquidar = array_diff($empleadosConMovimientos, $empleadosLiquidados);

        $totalPendienteCount = count($pendientesDeLiquidar);

        // Monto estimado (sumamos sus ingresos brutos para tener una idea)
        $totalPendienteMonto = (int) MovimientoNomina::whereIn('empleado_id', $pendientesDeLiquidar)
            ->where('empresa_id', $empresaId)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->where('estado', 'activo')
            ->where('es_ingreso', true)
            ->sum('monto');

        // ============ EVOLUCIÓN 6 MESES (Centrado en el mes elegido) ============
        $evolucionLabels = [];
        $evolucionSeries = [];

        foreach (range(5, 0) as $i) {
            $f = $fechaPeriodo->copy()->subMonths($i);

            $ing = (int) MovimientoNomina::where('empresa_id', $empresaId)
                ->where('anio', $f->year)->where('mes', $f->month)
                ->where('estado', 'activo')->where('es_ingreso', true)
                ->sum('monto');

            $des = (int) MovimientoNomina::where('empresa_id', $empresaId)
                ->where('anio', $f->year)->where('mes', $f->month)
                ->where('estado', 'activo')->where('es_ingreso', false)
                ->sum('monto');

            $baseIpsMes = (int) MovimientoNomina::where('empresa_id', $empresaId)
                ->where('anio', $f->year)->where('mes', $f->month)
                ->where('estado', 'activo')->where('es_ingreso', true)
                ->whereHas('empleado', function ($q) {
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
        $compIngresos   = [];
        $compDescuentos = [];

        foreach (range(5, 0) as $i) {
            $f = $fechaPeriodo->copy()->subMonths($i);

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

        // ============ ANÁLISIS DEMOGRÁFICO (Siempre basado en HOY) ============
        $hoy = Carbon::now();
        $empleadosActivos = Empleado::where('estado', 'activo');

        // --- Edad ---
        $edades = (clone $empleadosActivos)
            ->whereNotNull('fecha_nacimiento')
            ->pluck('fecha_nacimiento')
            ->map(fn($f) => Carbon::parse($f)->age)
            ->filter(fn($e) => $e >= 15 && $e <= 80)
            ->values();

        $edadPromedio = $edades->count() ? (int) round($edades->avg()) : 0;
        $edadMinima   = $edades->count() ? (int) $edades->min() : 0;
        $edadMaxima   = $edades->count() ? (int) $edades->max() : 0;

        $rangosEdad = ['18-25' => 0, '26-35' => 0, '36-45' => 0, '46-55' => 0, '56+' => 0];
        foreach ($edades as $e) {
            if ($e <= 25)      $rangosEdad['18-25']++;
            elseif ($e <= 35)  $rangosEdad['26-35']++;
            elseif ($e <= 45)  $rangosEdad['36-45']++;
            elseif ($e <= 55)  $rangosEdad['46-55']++;
            else               $rangosEdad['56+']++;
        }

        // --- Sexo ---
        $porSexoRaw = (clone $empleadosActivos)
            ->select('sexo', DB::raw('COUNT(*) as total'))
            ->groupBy('sexo')
            ->pluck('total', 'sexo')
            ->toArray();

        $porSexo = [
            'M'    => (int) ($porSexoRaw['M']    ?? 0),
            'F'    => (int) ($porSexoRaw['F']    ?? 0),
            'OTRO' => (int) ($porSexoRaw['OTRO'] ?? 0),
        ];
        $totalConSexo = array_sum($porSexo);

        // --- Antigüedad ---
        $antiguedades = (clone $empleadosActivos)
            ->whereNotNull('fecha_ingreso')
            ->pluck('fecha_ingreso')
            ->map(fn($f) => Carbon::parse($f)->diffInYears($hoy))
            ->values();

        $antiguedadPromedio = $antiguedades->count() ? round($antiguedades->avg(), 1) : 0;

        $rangosAntiguedad = ['< 1 año' => 0, '1-3 años' => 0, '4-6 años' => 0, '7-10 años' => 0, '10+ años' => 0];
        foreach ($antiguedades as $a) {
            if ($a < 1)       $rangosAntiguedad['< 1 año']++;
            elseif ($a <= 3)  $rangosAntiguedad['1-3 años']++;
            elseif ($a <= 6)  $rangosAntiguedad['4-6 años']++;
            elseif ($a <= 10) $rangosAntiguedad['7-10 años']++;
            else              $rangosAntiguedad['10+ años']++;
        }

        // --- Perfil por Departamento ---
        $perfilPorDepartamento = (clone $empleadosActivos)
            ->select('departamento_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('departamento_id')
            ->groupBy('departamento_id')
            ->with('departamento:id,nombre')
            ->get()
            ->map(function ($row) use ($empleadosActivos, $hoy) {
                $edadesDept = (clone $empleadosActivos)
                    ->where('departamento_id', $row->departamento_id)
                    ->whereNotNull('fecha_nacimiento')
                    ->pluck('fecha_nacimiento')
                    ->map(fn($f) => Carbon::parse($f)->age);
                return [
                    'departamento' => $row->departamento->nombre ?? '—',
                    'total'        => (int) $row->total,
                    'edad_prom'    => $edadesDept->count() ? (int) round($edadesDept->avg()) : 0,
                    'mujeres'      => (clone $empleadosActivos)->where('departamento_id', $row->departamento_id)->where('sexo', 'F')->count(),
                    'hombres'      => (clone $empleadosActivos)->where('departamento_id', $row->departamento_id)->where('sexo', 'M')->count(),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->toArray();

        // ============ AUSENCIAS DEL PERÍODO SELECCIONADO ============
        $movsAusenciaMes = MovimientoNomina::with(['empleado:id,fecha_nacimiento,nombres,apellidos'])
            ->where('empresa_id', $empresaId)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->where('estado', 'activo')
            ->whereIn('tipo_movimiento', ['ausencia', 'llegada_tardia'])
            ->get();

        $ausenciasPorEdad = ['18-25' => 0, '26-35' => 0, '36-45' => 0, '46-55' => 0, '56+' => 0, 'Sin dato' => 0];
        $totalAusenciasMes  = 0;
        $montoAusenciasMes  = 0;
        $empleadosConAusencia = [];

        foreach ($movsAusenciaMes as $mov) {
            $totalAusenciasMes++;
            $montoAusenciasMes += (int) $mov->monto;

            if ($mov->empleado) $empleadosConAusencia[$mov->empleado_id] = true;

            $fechaNac = optional($mov->empleado)->fecha_nacimiento;
            if (!$fechaNac) {
                $ausenciasPorEdad['Sin dato']++;
                continue;
            }
            $edad = \Carbon\Carbon::parse($fechaNac)->age;

            if ($edad <= 25)      $ausenciasPorEdad['18-25']++;
            elseif ($edad <= 35)  $ausenciasPorEdad['26-35']++;
            elseif ($edad <= 45)  $ausenciasPorEdad['36-45']++;
            elseif ($edad <= 55)  $ausenciasPorEdad['46-55']++;
            else                  $ausenciasPorEdad['56+']++;
        }

        $empleadosConAusenciaCount = count($empleadosConAusencia);

        $rangoMasAusencias = collect($ausenciasPorEdad)
            ->reject(fn($v, $k) => $k === 'Sin dato')
            ->sortDesc()
            ->take(1)
            ->keys()
            ->first() ?? '—';

        $cantidadRangoMasAusencias = $ausenciasPorEdad[$rangoMasAusencias] ?? 0;

        return view('dashboard.home', compact(
            'anio',
            'mes',
            'nombreMes',
            'totalEmpleadosActivos',
            'totalEmpleados',
            'totalDepartamentos',
            'totalCargos',
            'salarioPromedio',
            'nuevosIngresos',
            'porEstado',
            'porDepartamento',
            'empleados',
            // Financieros
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
            // Demográficos
            'edadPromedio',
            'edadMinima',
            'edadMaxima',
            'rangosEdad',
            'porSexo',
            'totalConSexo',
            'antiguedadPromedio',
            'rangosAntiguedad',
            'perfilPorDepartamento',
            // Ausencias
            'ausenciasPorEdad',
            'totalAusenciasMes',
            'montoAusenciasMes',
            'empleadosConAusenciaCount',
            'rangoMasAusencias',
            'cantidadRangoMasAusencias',
            // Gráficos
            'evolucionLabels',
            'evolucionSeries',
            'compLabels',
            'compIngresos',
            'compDescuentos'
        ));
    }

    /**
     * Exportar desglose completo de nómina a Excel para auditoría
     */
    public function exportarDesgloseNomina(Request $request)
    {
        $anio = $request->get('anio', date('Y'));
        $mes = $request->get('mes', date('m'));
        $empresaId = $request->get('empresa_id', 1);

        // 1. Traemos movimientos activos del mes
        $movimientos = MovimientoNomina::with('empleado')
            ->where('empresa_id', $empresaId)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->where('estado', 'activo')
            ->get();

        // 2. Agrupamos por empleado y calculamos
        $data = $movimientos->groupBy('empleado_id')->map(function ($items) use ($anio, $mes, $empresaId) {
            $empleado = $items->first()->empleado;

            // Sumas
            $sueldo = $items->where('tipo_movimiento', 'sueldo')->sum('monto');
            $extra = $items->where('tipo_movimiento', 'extra')->sum('monto');
            $vale = $items->where('tipo_movimiento', 'vale')->sum('monto');
            $ausencia = $items->where('tipo_movimiento', 'ausencia')->sum('monto');
            $llegada_tardia = $items->where('tipo_movimiento', 'llegada_tardia')->sum('monto');
            $otros = $items->where('tipo_movimiento', 'otros')->sum('monto');

            $bruto = $sueldo + $extra;
            $descuentos = $vale + $ausencia + $llegada_tardia + $otros;

            // IPS (Validamos si el empleado está afiliado)
            $parametroIps = ParametroIps::paraPeriodo($empresaId, $anio, $mes);
            $pctIps = (float) $parametroIps->aporte_empleado;

            $ips = 0;
            if ($empleado && $empleado->afiliado_ips) {
                $ips = round($bruto * ($pctIps / 100));
            }

            $neto = $bruto - $descuentos - $ips;

            return [
                'codigo' => $empleado->codigo_empleado ?? '-',
                'ci' => $empleado->numero_documento ?? '-',
                'colaborador' => $empleado->apellidos . ', ' . $empleado->nombres,
                'afiliado_ips' => ($empleado && $empleado->afiliado_ips) ? 'SÍ' : 'NO',
                'sueldo' => $sueldo,
                'extra' => $extra,
                'bruto' => $bruto,
                'vale' => $vale,
                'ausencia' => $ausencia,
                'llegada_tardia' => $llegada_tardia,
                'otros' => $otros,
                'total_descuentos' => $descuentos,
                'ips' => $ips,
                'neto' => $neto,
            ];
        })->sortBy('colaborador')->values();

        // 3. Generar el archivo Excel con fórmulas reales
        $filename = 'Desglose_Movimientos_' . $anio . '_' . str_pad($mes, 2, '0', STR_PAD_LEFT) . '.xls';

        $html = '<html><head><meta charset="UTF-8"></head><body>';
        $html .= '<h3>Desglose de Movimientos - Período ' . $mes . '/' . $anio . '</h3>';
        $html .= '<table border="1" style="font-family: Arial; font-size: 11px; text-align: center;">';

        // Cabeceras (Columnas de la A a la N)
        $html .= '<thead><tr style="background-color: #4F46E5; color: white;">';
        $html .= '<th>Código</th><th>CI</th><th>Colaborador</th><th>IPS?</th>'; // A, B, C, D
        $html .= '<th>Sueldo</th><th>Extras</th><th>Bruto</th>';                 // E, F, G
        $html .= '<th>Vales</th><th>Ausencias</th><th>Lleg. Tardía</th><th>Otros</th><th>Total Desc.</th>'; // H, I, J, K, L
        $html .= '<th>Retención IPS</th><th>NETO A PAGAR</th>';                  // M, N
        $html .= '</tr></thead><tbody>';

        $fila = 2; // Empezamos en la fila 2 (la 1 son las cabeceras)
        $totalBruto = 0;
        $totalIps = 0;
        $totalNeto = 0;

        foreach ($data as $row) {
            $totalBruto += $row['bruto'];
            $totalIps += $row['ips'];
            $totalNeto += $row['neto'];

            $html .= '<tr>';
            $html .= '<td>' . $row['codigo'] . '</td>'; // A
            $html .= '<td>' . $row['ci'] . '</td>';     // B
            $html .= '<td style="text-align: left;">' . $row['colaborador'] . '</td>'; // C
            $html .= '<td>' . $row['afiliado_ips'] . '</td>'; // D
            $html .= '<td>' . number_format($row['sueldo'], 0, ',', '.') . '</td>'; // E
            $html .= '<td>' . number_format($row['extra'], 0, ',', '.') . '</td>';  // F
            // Fórmula Bruto: E + F
            $html .= '<td style="background-color: #E0E7FF; font-weight: bold;" x:fmla="=E' . $fila . '+F' . $fila . '">' . number_format($row['bruto'], 0, ',', '.') . '</td>'; // G

            $html .= '<td>' . number_format($row['vale'], 0, ',', '.') . '</td>'; // H
            $html .= '<td>' . number_format($row['ausencia'], 0, ',', '.') . '</td>'; // I
            $html .= '<td>' . number_format($row['llegada_tardia'], 0, ',', '.') . '</td>'; // J
            $html .= '<td>' . number_format($row['otros'], 0, ',', '.') . '</td>'; // K
            // Fórmula Total Descuentos: H + I + J + K
            $html .= '<td x:fmla="=SUM(H' . $fila . ':K' . $fila . ')">' . number_format($row['total_descuentos'], 0, ',', '.') . '</td>'; // L

            $html .= '<td style="color: #DC2626;">' . number_format($row['ips'], 0, ',', '.') . '</td>'; // M
            // Fórmula Neto: G - L - M
            $html .= '<td style="background-color: #D1FAE5; font-weight: bold;" x:fmla="=G' . $fila . '-L' . $fila . '-M' . $fila . '">' . number_format($row['neto'], 0, ',', '.') . '</td>'; // N
            $html .= '</tr>';

            $fila++;
        }

        $ultimaFila = $fila - 1;

        // Fila de Totales con fórmulas SUMA de Excel
        $html .= '<tr style="background-color: #F3F4F6; font-weight: bold;">';
        $html .= '<td colspan="4" style="text-align: right;">TOTALES</td>';
        $html .= '<td x:fmla="=SUM(E2:E' . $ultimaFila . ')">' . number_format($data->sum('sueldo'), 0, ',', '.') . '</td>';
        $html .= '<td x:fmla="=SUM(F2:F' . $ultimaFila . ')">' . number_format($data->sum('extra'), 0, ',', '.') . '</td>';
        $html .= '<td x:fmla="=SUM(G2:G' . $ultimaFila . ')">' . number_format($totalBruto, 0, ',', '.') . '</td>';
        $html .= '<td x:fmla="=SUM(H2:H' . $ultimaFila . ')">' . number_format($data->sum('vale'), 0, ',', '.') . '</td>';
        $html .= '<td x:fmla="=SUM(I2:I' . $ultimaFila . ')">' . number_format($data->sum('ausencia'), 0, ',', '.') . '</td>';
        $html .= '<td x:fmla="=SUM(J2:J' . $ultimaFila . ')">' . number_format($data->sum('llegada_tardia'), 0, ',', '.') . '</td>';
        $html .= '<td x:fmla="=SUM(K2:K' . $ultimaFila . ')">' . number_format($data->sum('otros'), 0, ',', '.') . '</td>';
        $html .= '<td x:fmla="=SUM(L2:L' . $ultimaFila . ')">' . number_format($data->sum('total_descuentos'), 0, ',', '.') . '</td>';
        $html .= '<td x:fmla="=SUM(M2:M' . $ultimaFila . ')">' . number_format($totalIps, 0, ',', '.') . '</td>';
        $html .= '<td style="background-color: #A7F3D0;" x:fmla="=SUM(N2:N' . $ultimaFila . ')">' . number_format($totalNeto, 0, ',', '.') . '</td>';
        $html .= '</tr>';
        $html .= '</tbody></table></body></html>';

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
