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

        return view('HR.nomina.movimientos', compact('movimientos', 'empleados', 'anio', 'mes'));
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

            // Si el monto viene vacío o es 0, usar el predeterminado
            $monto = (int) $request->monto;
            if ($monto === 0) {
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

            // Si el monto viene vacío o es 0, usar el predeterminado
            $monto = (int) $request->monto;
            if ($monto === 0) {
                $monto = MovimientoNomina::getMontoPredeterminado($tipo);
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

        $resumen = $movimientos
            ->groupBy('empleado_id')
            ->map(function ($items) {
                $empleado = $items->first()->empleado;
                $sueldo        = $items->where('tipo_movimiento', 'sueldo')->sum('monto');
                $extra         = $items->where('tipo_movimiento', 'extra')->sum('monto');
                $vale          = $items->where('tipo_movimiento', 'vale')->sum('monto');
                $ausencia      = $items->where('tipo_movimiento', 'ausencia')->sum('monto');
                $llegadaTardia = $items->where('tipo_movimiento', 'llegada_tardia')->sum('monto');
                $otros         = $items->where('tipo_movimiento', 'otros')->sum('monto');

                return [
                    'empleado'       => $empleado,
                    'sueldo'         => $sueldo,
                    'extra'          => $extra,
                    'vale'           => $vale,
                    'ausencia'       => $ausencia,
                    'llegada_tardia' => $llegadaTardia,
                    'otros'          => $otros,
                    'total_neto'     => ($sueldo + $extra) - ($vale + $ausencia + $llegadaTardia + $otros),
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

        // Traer movimientos activos del período
        $movimientos = MovimientoNomina::with(['empleado'])
            ->where('empresa_id', $empresaId)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->where('estado', 'activo')
            ->get();

        // Filtro por empleado (opcional desde el select)
        $empleadoFiltro = $request->get('empleado_id');
        if (!empty($empleadoFiltro)) {
            $movimientos = $movimientos->where('empleado_id', $empleadoFiltro);
        }

        // Agrupar y sumar
        $resumen = $movimientos
            ->groupBy('empleado_id')
            ->map(function ($items) {
                $empleado = $items->first()->empleado;

                $sueldo        = $items->where('tipo_movimiento', 'sueldo')->sum('monto');
                $extra         = $items->where('tipo_movimiento', 'extra')->sum('monto');
                $vale          = $items->where('tipo_movimiento', 'vale')->sum('monto');
                $ausencia      = $items->where('tipo_movimiento', 'ausencia')->sum('monto');
                $llegadaTardia = $items->where('tipo_movimiento', 'llegada_tardia')->sum('monto');
                $otros         = $items->where('tipo_movimiento', 'otros')->sum('monto');

                return [
                    'empleado_id'     => $empleado->id ?? null,
                    'colaborador'     => $empleado
                        ? $empleado->nombres . ' ' . $empleado->apellidos
                        : '-',
                    'ci'              => $empleado->numero_documento ?? '-',
                    'iniciales'       => $empleado
                        ? strtoupper(substr($empleado->nombres, 0, 1)) . strtoupper(substr($empleado->apellidos, 0, 1))
                        : '--',
                    'sueldo'          => $sueldo,
                    'extra'           => $extra,
                    'vale'            => $vale,
                    'ausencia'        => $ausencia,
                    'llegada_tardia'  => $llegadaTardia,
                    'otros'           => $otros,
                    'total_neto'      => ($sueldo + $extra) - ($vale + $ausencia + $llegadaTardia + $otros),
                ];
            })
            ->sortBy('colaborador')
            ->values();

        // Búsqueda global (DataTable)
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = strtolower($request->search['value']);
            $resumen = $resumen->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['colaborador']), $search)
                    || str_contains(strtolower($item['ci']), $search);
            })->values();
        }

        // Total general (después de filtros)
        $totalGeneral = $resumen->sum('total_neto');

        // Paginación manual
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
}
