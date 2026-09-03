<?php
// app/Http/Controllers/NominaController.php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\MovimientoNomina;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NominaController extends Controller
{
    /**
     * ============================================================
     * MOVIMIENTOS DE NÓMINA - LISTADO CON DATATABLE
     * ============================================================
     */
    public function movimientos(Request $request)
    {
        $anio = $request->get('anio', date('Y'));
        $mes  = $request->get('mes', date('m'));
        $empresaId = $request->get('empresa_id', 1); // o auth()->user()->empresa_id

        $empleados = Empleado::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->orderBy('apellidos')
            ->get();

        return view('HR.nomina.movimientos', compact('anio', 'mes', 'empresaId', 'empleados'));
    }

    /**
     * Datos JSON para DataTable (Server-Side)
     */
    public function movimientosData(Request $request)
    {
        $anio      = $request->get('anio', date('Y'));
        $mes       = $request->get('mes', date('m'));
        $empresaId = $request->get('empresa_id', 1);

        $query = MovimientoNomina::with(['empleado'])
            ->empresa($empresaId)
            ->periodo($anio, $mes)
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc');

        // Filtro de búsqueda global
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('empleado', function ($sq) use ($search) {
                    $sq->where('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('numero_documento', 'like', "%{$search}%");
                })
                    ->orWhere('tipo_movimiento', 'like', "%{$search}%")
                    ->orWhere('observacion', 'like', "%{$search}%");
            });
        }

        // Filtro por tipo de movimiento
        if ($request->has('tipo') && !empty($request->tipo)) {
            $query->where('tipo_movimiento', $request->tipo);
        }

        // Filtro por empleado
        if ($request->has('empleado_id') && !empty($request->empleado_id)) {
            $query->where('empleado_id', $request->empleado_id);
        }

        $totalRecords = $query->count();

        // Paginación
        $start  = $request->get('start', 0);
        $length = $request->get('length', 10);
        $data   = $query->skip($start)->take($length)->get();

        $records = $data->map(function ($mov) {
            $empleado = $mov->empleado;
            return [
                'id'              => $mov->id,
                'fecha'           => $mov->fecha->format('d/m/Y'),
                'ci'              => $empleado ? $empleado->numero_documento : '-',
                'empleado'        => $empleado ? "{$empleado->nombres} {$empleado->apellidos}" : '-',
                'empleado_id'     => $mov->empleado_id,
                'tipo_movimiento' => $mov->tipo_movimiento,
                'tipo_label'      => $this->tipoLabel($mov->tipo_movimiento),
                'monto'           => number_format($mov->monto, 0, ',', '.'),
                'naturaleza'      => $mov->naturaleza_badge,
                'estado'          => $mov->estado_badge,
                'estado_raw'      => $mov->estado,
                'observacion'     => $mov->observacion ?: '-',
                'es_ingreso'      => $mov->es_ingreso,
                'monto_raw'       => $mov->monto,
            ];
        });

        return response()->json([
            'draw'            => $request->get('draw', 1),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data'            => $records,
        ]);
    }

    /**
     * Guardar nuevo movimiento de nómina
     */
    public function store(Request $request)
    {
        $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'tipo_movimiento' => 'required|in:sueldo,extra,vale,ausencia,llegada_tardia,otros', // Cambiado de 'tipo' a 'tipo_movimiento'
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'observacion' => 'nullable|string|max:500',
            // 'anio' y 'mes' los calcularemos automáticamente
        ]);

        // Limpiar separadores de miles
        $montoRaw = str_replace(['.', ','], '', $request->monto);
        $monto = (int) $montoRaw;

        // Obtener año y mes de la fecha
        $fecha = \Carbon\Carbon::parse($request->fecha);
        $anio = $fecha->year;
        $mes = $fecha->month;

        // Determinar naturaleza según el tipo de movimiento
        $esIngreso = MovimientoNomina::determinarNaturaleza($request->tipo_movimiento);

        try {
            $movimiento = MovimientoNomina::create([
                'empleado_id' => $request->empleado_id,
                'empresa_id' => $request->empresa_id ?? 1,
                'fecha' => $fecha->toDateString(),
                'tipo_movimiento' => $request->tipo_movimiento,
                'monto' => $monto,
                'anio' => $anio,
                'mes' => $mes,
                'es_ingreso' => $esIngreso,
                'observacion' => $request->observacion,
                'estado' => 'activo',
                'creado_por' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Movimiento registrado correctamente.',
                'data' => $movimiento,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al guardar movimiento de nómina: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error interno al guardar el movimiento.',
            ], 500);
        }
    }

    /**
     * Anular movimiento
     */
    public function anularMovimiento($id)
    {
        $movimiento = MovimientoNomina::findOrFail($id);

        if ($movimiento->estado === 'anulado') {
            return response()->json([
                'success' => false,
                'message' => 'El movimiento ya se encuentra anulado.',
            ], 422);
        }

        $movimiento->update([
            'estado'         => 'anulado',
            'actualizado_por' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Movimiento anulado correctamente.',
        ]);
    }

    /**
     * Obtener salario base del empleado (para autocompletar en modal)
     */
    public function getEmpleadoSalario($id)
    {
        $empleado = Empleado::select('id', 'nombres', 'apellidos', 'salario_base')
            ->findOrFail($id);

        return response()->json([
            'id'           => $empleado->id,
            'nombre'       => "{$empleado->nombres} {$empleado->apellidos}",
            'salario_base' => $empleado->salario_base,
        ]);
    }

    /**
     * ============================================================
     * RESUMEN DE NÓMINA - VISTA POR COLABORADOR
     * ============================================================
     */
    public function resumen(Request $request)
    {
        $anio      = $request->get('anio', date('Y'));
        $mes       = $request->get('mes', date('m'));
        $empresaId = $request->get('empresa_id', 1);

        // Obtener todos los empleados activos de la empresa
        $empleados = Empleado::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->orderBy('apellidos')
            ->get();

        $resumen = [];
        $totalNetoGeneral = 0;

        foreach ($empleados as $emp) {
            $movimientos = MovimientoNomina::where('empleado_id', $emp->id)
                ->empresa($empresaId)
                ->periodo($anio, $mes)
                ->where('estado', 'activo')
                ->get();

            $sueldo = (int) $movimientos->where('tipo_movimiento', 'sueldo')->sum('monto');
            $extra  = (int) $movimientos->where('tipo_movimiento', 'extra')->sum('monto');
            $vale   = (int) $movimientos->where('tipo_movimiento', 'vale')->sum('monto');
            $ausencia = (int) $movimientos->where('tipo_movimiento', 'ausencia')->sum('monto');
            $llegadaTardia = (int) $movimientos->where('tipo_movimiento', 'llegada_tardia')->sum('monto');
            $otros  = (int) $movimientos->where('tipo_movimiento', 'otros')->sum('monto');

            $totalIngresos = $sueldo + $extra;
            $totalDescuentos = $vale + $ausencia + $llegadaTardia + $otros;
            $totalNeto = $totalIngresos - $totalDescuentos;

            $resumen[] = [
                'empleado'      => $emp,
                'sueldo'        => $sueldo,
                'extra'         => $extra,
                'vale'          => $vale,
                'ausencia'      => $ausencia,
                'llegada_tardia' => $llegadaTardia,
                'otros'         => $otros,
                'total_neto'    => $totalNeto,
            ];

            $totalNetoGeneral += $totalNeto;
        }

        return view('HR.nomina.resumen', compact(
            'anio',
            'mes',
            'empresaId',
            'resumen',
            'totalNetoGeneral'
        ));
    }

    /**
     * Exportar resumen a Excel (placeholder - implementar con maatwebsite/excel)
     */
    public function exportarExcel(Request $request)
    {
        $anio = $request->get('anio', date('Y'));
        $mes  = $request->get('mes', date('m'));

        // Aquí iría la lógica de exportación con maatwebsite/excel
        // Por ahora redirigimos con mensaje
        return redirect()->back()->with('info', 'Función de exportación a Excel en desarrollo.');
    }

    /**
     * Exportar resumen a CSV
     */
    public function exportarCsv(Request $request)
    {
        $anio      = $request->get('anio', date('Y'));
        $mes       = $request->get('mes', date('m'));
        $empresaId = $request->get('empresa_id', 1);

        $empleados = Empleado::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->orderBy('apellidos')
            ->get();

        $filename = "resumen_nomina_{$anio}_{$mes}.csv";
        $headers = [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($empleados, $anio, $mes, $empresaId) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['COLABORADOR', 'SUELDO', 'EXTRA', 'VALE', 'AUSENCIA', 'LLEGADA TARDIA', 'OTROS', 'TOTAL NETO']);

            foreach ($empleados as $emp) {
                $movimientos = MovimientoNomina::where('empleado_id', $emp->id)
                    ->empresa($empresaId)
                    ->periodo($anio, $mes)
                    ->where('estado', 'activo')
                    ->get();

                $sueldo = $movimientos->where('tipo_movimiento', 'sueldo')->sum('monto');
                $extra  = $movimientos->where('tipo_movimiento', 'extra')->sum('monto');
                $vale   = $movimientos->where('tipo_movimiento', 'vale')->sum('monto');
                $ausencia = $movimientos->where('tipo_movimiento', 'ausencia')->sum('monto');
                $llegadaTardia = $movimientos->where('tipo_movimiento', 'llegada_tardia')->sum('monto');
                $otros  = $movimientos->where('tipo_movimiento', 'otros')->sum('monto');
                $totalNeto = ($sueldo + $extra) - ($vale + $ausencia + $llegadaTardia + $otros);

                fputcsv($file, [
                    "{$emp->nombres} {$emp->apellidos}",
                    $sueldo,
                    $extra,
                    $vale,
                    $ausencia,
                    $llegadaTardia,
                    $otros,
                    $totalNeto,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper: etiqueta legible para tipo de movimiento
     */
    private function tipoLabel(string $tipo): string
    {
        $labels = [
            'sueldo'          => 'Sueldo',
            'extra'           => 'Extra',
            'vale'            => 'Vale',
            'ausencia'        => 'Ausencia',
            'llegada_tardia'  => 'Llegada Tardía',
            'otros'           => 'Otros',
        ];
        return $labels[$tipo] ?? ucfirst($tipo);
    }
}
