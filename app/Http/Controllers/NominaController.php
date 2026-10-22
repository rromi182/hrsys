<?php
// app/Http/Controllers/NominaController.php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\MovimientoNomina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NominaController extends Controller
{
    public function movimientos(Request $request)
    {
        $anio = $request->get('anio', date('Y'));
        $mes = $request->get('mes', date('m'));
        $empresaId = $request->get('empresa_id', 1);

        $query = MovimientoNomina::with(['empleado'])
            ->where('empresa_id', $empresaId)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->where('estado', 'activo')
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('empleado_id')) {
            $query->where('empleado_id', $request->empleado_id);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo_movimiento', $request->tipo);
        }

        $movimientos = $query->get();

        $empleados = Empleado::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->orderBy('apellidos')
            ->get();

        return view('HR.nomina.movimientos', compact('movimientos', 'empleados', 'anio', 'mes', 'empresaId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'tipo_movimiento' => 'required|in:sueldo,extra,vale,ausencia,llegada_tardia,otros',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'observacion' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $fecha = \Carbon\Carbon::parse($request->fecha);
            $tipo = $request->tipo_movimiento;
            $esIngreso = MovimientoNomina::determinarNaturaleza($tipo);

            $empleado = Empleado::findOrFail($request->empleado_id);
            $monto = (int) $request->monto;

            if ($monto === 0 && in_array($tipo, MovimientoNomina::TIPOS_INGRESO, true)) {
                $monto = MovimientoNomina::montoBasePara($empleado, $tipo);
            } elseif ($monto === 0) {
                $monto = MovimientoNomina::getMontoPredeterminado($tipo);
            }

            MovimientoNomina::create([
                'empleado_id' => $request->empleado_id,
                'empresa_id' => $request->empresa_id ?? 1,
                'fecha' => $fecha->toDateString(),
                'tipo_movimiento' => $tipo,
                'monto' => $monto,
                'anio' => $fecha->year,
                'mes' => $fecha->month,
                'es_ingreso' => $esIngreso,
                'generado_automaticamente' => false,
                'observacion' => $request->observacion,
                'estado' => 'activo',
                'creado_por' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('nomina.movimientos')
                ->with('success', 'Movimiento registrado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al guardar movimiento: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al guardar el movimiento: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar formulario para editar un movimiento (devuelve JSON para el modal)
     */
    public function edit($id)
    {
        $movimiento = MovimientoNomina::with(['empleado'])->findOrFail($id);

        // Verificar que no esté anulado
        if ($movimiento->estado === 'anulado') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede editar un movimiento anulado.'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $movimiento->id,
                'empleado_id' => $movimiento->empleado_id,
                'empleado_nombre' => $movimiento->empleado ? $movimiento->empleado->apellidos . ', ' . $movimiento->empleado->nombres : '',
                'fecha' => $movimiento->fecha->format('Y-m-d'),
                'tipo_movimiento' => $movimiento->tipo_movimiento,
                'monto' => $movimiento->monto,
                'observacion' => $movimiento->observacion,
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'tipo_movimiento' => 'required|in:sueldo,extra,vale,ausencia,llegada_tardia,otros',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'observacion' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $movimiento = MovimientoNomina::findOrFail($id);

            if ($movimiento->estado === 'anulado') {
                return redirect()->back()
                    ->with('error', 'No se puede editar un movimiento anulado.');
            }

            $fecha = \Carbon\Carbon::parse($request->fecha);
            $tipo = $request->tipo_movimiento;
            $esIngreso = MovimientoNomina::determinarNaturaleza($tipo);

            $empleado = Empleado::findOrFail($request->empleado_id);
            $monto = (int) $request->monto;

            if ($monto === 0 && in_array($tipo, MovimientoNomina::TIPOS_INGRESO, true)) {
                $monto = MovimientoNomina::montoBasePara($empleado, $tipo);
            }

            $movimiento->update([
                'empleado_id' => $request->empleado_id,
                'fecha' => $fecha->toDateString(),
                'tipo_movimiento' => $tipo,
                'monto' => $monto,
                'anio' => $fecha->year,
                'mes' => $fecha->month,
                'es_ingreso' => $esIngreso,
                'observacion' => $request->observacion,
                'actualizado_por' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('nomina.movimientos')
                ->with('success', 'Movimiento actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar movimiento: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al actualizar el movimiento: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function anularMovimiento($id)
    {
        try {
            DB::beginTransaction();

            $movimiento = MovimientoNomina::findOrFail($id);

            if ($movimiento->estado === 'anulado') {
                return redirect()->back()
                    ->with('error', 'El movimiento ya está anulado.');
            }

            $movimiento->update([
                'estado' => 'anulado',
                'actualizado_por' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('nomina.movimientos')
                ->with('success', 'Movimiento anulado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Error al anular el movimiento.');
        }
    }

    /**
     * Generar movimientos de sueldo y extra para todos los empleados activos del período.
     * Es idempotente: si un movimiento ya existe, se omite.
     */
    public function generarMensual(Request $request)
    {
        $request->validate([
            'anio' => 'required|integer|between:2020,2100',
            'mes'  => 'required|integer|between:1,12',
        ]);

        try {
            $resultado = app(\App\Services\GeneradorMovimientosNomina::class)
                ->generarMes(
                    (int) ($request->empresa_id ?? 1),
                    (int) $request->anio,
                    (int) $request->mes,
                    auth()->id()
                );

            $msg = "✅ Se generaron {$resultado['creados']} movimientos.";
            if ($resultado['omitidos'] > 0) {
                $msg .= " Se omitieron {$resultado['omitidos']} que ya existían.";
            }
            if ($resultado['creados'] === 0 && $resultado['omitidos'] > 0) {
                $msg = "ℹ️ Todos los movimientos del período ya existían. Nada que generar.";
            }

            return redirect()
                ->route('nomina.movimientos', [
                    'anio' => $request->anio,
                    'mes'  => $request->mes,
                ])
                ->with('success', $msg);
        } catch (\Throwable $e) {
            \Log::error('Error al generar movimientos de nómina: ' . $e->getMessage(), [
                'anio' => $request->anio,
                'mes'  => $request->mes,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('nomina.movimientos', [
                    'anio' => $request->anio,
                    'mes'  => $request->mes,
                ])
                ->with('error', 'Error al generar movimientos: ' . $e->getMessage());
        }
    }

    /**
     * Vista de resumen por empleado
     */
    public function resumen(Request $request)
    {
        $anio = (int) $request->get('anio', date('Y'));
        $mes  = (int) $request->get('mes', date('m'));
        $empresaId = $request->get('empresa_id', 1);

        $empleados = Empleado::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->orderBy('apellidos')
            ->get();

        return view('HR.nomina.resumen', compact('anio', 'mes', 'empresaId', 'empleados'));
    }

    /**
     * Exportar resumen a Excel (HTML table con headers de Excel)
     */
    public function exportarExcel(Request $request)
    {
        $data = $this->getResumenData($request);

        $filename = 'resumen_nomina_' . $data['anio'] . '_' . str_pad($data['mes'], 2, '0', STR_PAD_LEFT) . '.xls';

        $html = '<html><head><meta charset="UTF-8"></head><body>';
        $html .= '<table border="1">';
        $html .= '<thead><tr style="background:#f0f0f0;font-weight:bold;">';
        $html .= '<th>Colaborador</th><th>Sueldo</th><th>Extra</th><th>Vale</th>';
        $html .= '<th>Ausencia</th><th>Llegada Tardía</th><th>Otros</th><th>Total Neto</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($data['resumen'] as $item) {
            $html .= '<tr>';
            $html .= '<td>' . e($item['empleado']->nombres . ' ' . $item['empleado']->apellidos) . '</td>';
            $html .= '<td>' . number_format($item['sueldo'], 0, ',', '.') . '</td>';
            $html .= '<td>' . number_format($item['extra'], 0, ',', '.') . '</td>';
            $html .= '<td>' . number_format($item['vale'], 0, ',', '.') . '</td>';
            $html .= '<td>' . number_format($item['ausencia'], 0, ',', '.') . '</td>';
            $html .= '<td>' . number_format($item['llegada_tardia'], 0, ',', '.') . '</td>';
            $html .= '<td>' . number_format($item['otros'], 0, ',', '.') . '</td>';
            $html .= '<td>' . number_format($item['total_neto'], 0, ',', '.') . '</td>';
            $html .= '</tr>';
        }

        $html .= '<tr style="font-weight:bold;background:#e0e0e0;">';
        $html .= '<td colspan="7" style="text-align:right;">TOTAL GENERAL</td>';
        $html .= '<td>' . number_format($data['totalNetoGeneral'], 0, ',', '.') . '</td>';
        $html .= '</tr>';

        $html .= '</tbody></table></body></html>';

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Exportar resumen a CSV
     */
    public function exportarCsv(Request $request)
    {
        $data = $this->getResumenData($request);

        $filename = 'resumen_nomina_' . $data['anio'] . '_' . str_pad($data['mes'], 2, '0', STR_PAD_LEFT) . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            // BOM para Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Colaborador',
                'Sueldo',
                'Extra',
                'Vale',
                'Ausencia',
                'Llegada Tardía',
                'Otros',
                'Total Neto'
            ]);

            foreach ($data['resumen'] as $item) {
                fputcsv($file, [
                    $item['empleado']->nombres . ' ' . $item['empleado']->apellidos,
                    $item['sueldo'],
                    $item['extra'],
                    $item['vale'],
                    $item['ausencia'],
                    $item['llegada_tardia'],
                    $item['otros'],
                    $item['total_neto'],
                ]);
            }

            fputcsv($file, ['TOTAL GENERAL', '', '', '', '', '', '', $data['totalNetoGeneral']]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Reporte individual de un colaborador (vista imprimible)
     */
    public function reporteIndividual(Request $request, $empleadoId)
    {
        $anio = (int) $request->get('anio', date('Y'));
        $mes  = (int) $request->get('mes', date('m'));
        $empresaId = $request->get('empresa_id', 1);

        $empleado = Empleado::findOrFail($empleadoId);

        $movimientos = MovimientoNomina::where('empleado_id', $empleadoId)
            ->where('empresa_id', $empresaId)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->where('estado', 'activo')
            ->orderBy('fecha')
            ->get();

        $resumen = [
            'sueldo'         => $movimientos->where('tipo_movimiento', 'sueldo')->sum('monto'),
            'extra'          => $movimientos->where('tipo_movimiento', 'extra')->sum('monto'),
            'vale'           => $movimientos->where('tipo_movimiento', 'vale')->sum('monto'),
            'ausencia'       => $movimientos->where('tipo_movimiento', 'ausencia')->sum('monto'),
            'llegada_tardia' => $movimientos->where('tipo_movimiento', 'llegada_tardia')->sum('monto'),
            'otros'          => $movimientos->where('tipo_movimiento', 'otros')->sum('monto'),
        ];

        $totalNeto = ($resumen['sueldo'] + $resumen['extra'])
            - ($resumen['vale'] + $resumen['ausencia'] + $resumen['llegada_tardia'] + $resumen['otros']);

        return view('HR.nomina.reporte-individual', compact(
            'empleado',
            'movimientos',
            'resumen',
            'totalNeto',
            'anio',
            'mes'
        ));
    }

    /**
     * Helper privado: devuelve los datos del resumen (reutilizable)
     */
    private function getResumenData(Request $request): array
    {
        $anio = (int) $request->get('anio', date('Y'));
        $mes  = (int) $request->get('mes', date('m'));
        $empresaId = $request->get('empresa_id', 1);

        $movimientos = MovimientoNomina::with(['empleado'])
            ->where('empresa_id', $empresaId)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->where('estado', 'activo')
            ->get();

        // Obtenemos el % de IPS para el período
        $parametroIps = \App\Models\ParametroIps::paraPeriodo($empresaId, $anio, $mes);
        $pctIps = (float) $parametroIps->aporte_empleado;

        $resumen = $movimientos
            ->groupBy('empleado_id')
            ->map(function ($items) use ($pctIps) {
                $empleado = $items->first()->empleado;
                $sueldo        = $items->where('tipo_movimiento', 'sueldo')->sum('monto');
                $extra         = $items->where('tipo_movimiento', 'extra')->sum('monto');
                $vale          = $items->where('tipo_movimiento', 'vale')->sum('monto');
                $ausencia      = $items->where('tipo_movimiento', 'ausencia')->sum('monto');
                $llegadaTardia = $items->where('tipo_movimiento', 'llegada_tardia')->sum('monto');
                $otros         = $items->where('tipo_movimiento', 'otros')->sum('monto');

                // Cálculo del IPS
                $bruto = $sueldo + $extra;
                $ips = 0;
                if ($empleado && $empleado->afiliado_ips) {
                    $ips = (int) round($bruto * ($pctIps / 100));
                }

                return [
                    'empleado'       => $empleado,
                    'sueldo'         => $sueldo,
                    'extra'          => $extra,
                    'vale'           => $vale,
                    'ausencia'       => $ausencia,
                    'llegada_tardia' => $llegadaTardia,
                    'otros'          => $otros,
                    'ips'            => $ips,
                    'total_neto'     => $bruto - ($vale + $ausencia + $llegadaTardia + $otros) - $ips,
                ];
            })
            ->sortBy(fn($i) => $i['empleado']->apellidos ?? '')
            ->values();

        return [
            'resumen'          => $resumen,
            'totalNetoGeneral' => $resumen->sum('total_neto'),
            'anio'             => $anio,
            'mes'              => $mes,
        ];
    }

    /**
     * Datos JSON para DataTable de resumen (server-side)
     */
    public function resumenData(Request $request)
    {
        $anio      = (int) $request->get('anio', date('Y'));
        $mes       = (int) $request->get('mes', date('m'));
        $empresaId = $request->get('empresa_id', 1);

        $movimientos = MovimientoNomina::with(['empleado'])
            ->where('empresa_id', $empresaId)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->where('estado', 'activo')
            ->get();

        $empleadoFiltro = $request->get('empleado_id');
        if (!empty($empleadoFiltro)) {
            $movimientos = $movimientos->where('empleado_id', $empleadoFiltro);
        }

        // Obtenemos el % de IPS
        $parametroIps = \App\Models\ParametroIps::paraPeriodo($empresaId, $anio, $mes);
        $pctIps = (float) $parametroIps->aporte_empleado;

        $resumen = $movimientos
            ->groupBy('empleado_id')
            ->map(function ($items) use ($pctIps) {
                $empleado = $items->first()->empleado;

                $sueldo        = $items->where('tipo_movimiento', 'sueldo')->sum('monto');
                $extra         = $items->where('tipo_movimiento', 'extra')->sum('monto');
                $vale          = $items->where('tipo_movimiento', 'vale')->sum('monto');
                $ausencia      = $items->where('tipo_movimiento', 'ausencia')->sum('monto');
                $llegadaTardia = $items->where('tipo_movimiento', 'llegada_tardia')->sum('monto');
                $otros         = $items->where('tipo_movimiento', 'otros')->sum('monto');

                $bruto = $sueldo + $extra;
                $ips = 0;
                if ($empleado && $empleado->afiliado_ips) {
                    $ips = (int) round($bruto * ($pctIps / 100));
                }

                return [
                    'empleado_id'     => $empleado->id ?? null,
                    'colaborador'     => $empleado ? $empleado->nombres . ' ' . $empleado->apellidos : '-',
                    'ci'              => $empleado->numero_documento ?? '-',
                    'iniciales'       => $empleado ? strtoupper(substr($empleado->nombres, 0, 1)) . strtoupper(substr($empleado->apellidos, 0, 1)) : '--',
                    'sueldo'          => $sueldo,
                    'extra'           => $extra,
                    'vale'            => $vale,
                    'ausencia'        => $ausencia,
                    'llegada_tardia'  => $llegadaTardia,
                    'otros'           => $otros,
                    'ips'             => $ips,
                    'total_neto'      => $bruto - ($vale + $ausencia + $llegadaTardia + $otros) - $ips,
                ];
            })
            ->sortBy('colaborador')
            ->values();

        if ($request->has('search') && !empty($request->search['value'])) {
            $search = strtolower($request->search['value']);
            $resumen = $resumen->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['colaborador']), $search) || str_contains(strtolower($item['ci']), $search);
            })->values();
        }

        $totalGeneral = $resumen->sum('total_neto');

        $start  = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 10);
        $data   = $resumen->slice($start, $length)->values();

        return response()->json([
            'draw'            => (int) $request->get('draw', 1),
            'recordsTotal'    => $resumen->count(),
            'recordsFiltered' => $resumen->count(),
            'totalGeneral'    => $totalGeneral,
            'data'            => $data,
        ]);
    }

    /**
     * Exportar desglose completo de nómina a Excel para auditoría
     */
    public function exportarDesgloseNomina(Request $request)
    {
        $anio = $request->get('anio', date('Y'));
        $mes = $request->get('mes', date('m'));
        $empresaId = $request->get('empresa_id', 1);

        $movimientos = MovimientoNomina::with('empleado')
            ->where('empresa_id', $empresaId)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->where('estado', 'activo')
            ->get();

        $data = $movimientos->groupBy('empleado_id')->map(function ($items) use ($anio, $mes, $empresaId) {
            $empleado = $items->first()->empleado;

            $sueldo = $items->where('tipo_movimiento', 'sueldo')->sum('monto');
            $extra = $items->where('tipo_movimiento', 'extra')->sum('monto');
            $vale = $items->where('tipo_movimiento', 'vale')->sum('monto');
            $ausencia = $items->where('tipo_movimiento', 'ausencia')->sum('monto');
            $llegada_tardia = $items->where('tipo_movimiento', 'llegada_tardia')->sum('monto');
            $otros = $items->where('tipo_movimiento', 'otros')->sum('monto');

            $bruto = $sueldo + $extra;
            $descuentos = $vale + $ausencia + $llegada_tardia + $otros;

            $parametroIps = \App\Models\ParametroIps::paraPeriodo($empresaId, $anio, $mes);
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

        $filename = 'Desglose_Movimientos_' . $anio . '_' . str_pad($mes, 2, '0', STR_PAD_LEFT) . '.xls';

        $html = '<html><head><meta charset="UTF-8"></head><body>';
        $html .= '<h3>Desglose de Movimientos - Período ' . $mes . '/' . $anio . '</h3>';
        $html .= '<table border="1" style="font-family: Arial; font-size: 11px; text-align: center;">';

        $html .= '<thead><tr style="background-color: #4F46E5; color: white;">';
        $html .= '<th>Código</th><th>CI</th><th>Colaborador</th><th>IPS?</th>';
        $html .= '<th>Sueldo</th><th>Extras</th><th>Bruto</th>';
        $html .= '<th>Vales</th><th>Ausencias</th><th>Lleg. Tardía</th><th>Otros</th><th>Total Desc.</th>';
        $html .= '<th>Retención IPS</th><th>NETO A PAGAR</th>';
        $html .= '</tr></thead><tbody>';

        $fila = 2;
        $totalBruto = 0;
        $totalIps = 0;
        $totalNeto = 0;

        foreach ($data as $row) {
            $totalBruto += $row['bruto'];
            $totalIps += $row['ips'];
            $totalNeto += $row['neto'];

            $html .= '<tr>';
            $html .= '<td>' . $row['codigo'] . '</td>';
            $html .= '<td>' . $row['ci'] . '</td>';
            $html .= '<td style="text-align: left;">' . $row['colaborador'] . '</td>';
            $html .= '<td>' . $row['afiliado_ips'] . '</td>';
            $html .= '<td>' . number_format($row['sueldo'], 0, ',', '.') . '</td>';
            $html .= '<td>' . number_format($row['extra'], 0, ',', '.') . '</td>';
            $html .= '<td style="background-color: #E0E7FF; font-weight: bold;" x:fmla="=E' . $fila . '+F' . $fila . '">' . number_format($row['bruto'], 0, ',', '.') . '</td>';
            $html .= '<td>' . number_format($row['vale'], 0, ',', '.') . '</td>';
            $html .= '<td>' . number_format($row['ausencia'], 0, ',', '.') . '</td>';
            $html .= '<td>' . number_format($row['llegada_tardia'], 0, ',', '.') . '</td>';
            $html .= '<td>' . number_format($row['otros'], 0, ',', '.') . '</td>';
            $html .= '<td x:fmla="=SUM(H' . $fila . ':K' . $fila . ')">' . number_format($row['total_descuentos'], 0, ',', '.') . '</td>';
            $html .= '<td style="color: #DC2626;">' . number_format($row['ips'], 0, ',', '.') . '</td>';
            $html .= '<td style="background-color: #D1FAE5; font-weight: bold;" x:fmla="=G' . $fila . '-L' . $fila . '-M' . $fila . '">' . number_format($row['neto'], 0, ',', '.') . '</td>';
            $html .= '</tr>';
            $fila++;
        }

        $ultimaFila = $fila - 1;

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
