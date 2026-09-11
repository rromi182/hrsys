<?php
// app/Http/Controllers/LiquidacionController.php
namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\ConceptoSalarial;
use App\Models\LiquidacionSalarial;
use App\Models\LiquidacionSalarialDet;
use App\Models\MovimientoNomina;
use App\Models\ParametroIps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LiquidacionController extends Controller
{
    /**
     * Listado de liquidaciones (vista con DataTable).
     */
    public function index(Request $request)
    {
        $anio      = (int) $request->get('anio', date('Y'));
        $mes       = (int) $request->get('mes', date('m'));
        $empresaId = $request->get('empresa_id', 1);

        $empleados = Empleado::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->orderBy('apellidos')
            ->get();

        return view('HR.liquidacion.listado', compact(
            'anio', 'mes', 'empresaId', 'empleados'
        ));
    }

    /**
     * Datos JSON para el DataTable.
     */
    public function data(Request $request)
    {
        $anio      = (int) $request->get('anio', date('Y'));
        $mes       = (int) $request->get('mes', date('m'));
        $empresaId = $request->get('empresa_id', 1);

        $query = LiquidacionSalarial::with(['empleado'])
            ->where('empresa_id', $empresaId)
            ->where('periodo_anio', $anio)
            ->where('periodo_mes', $mes)
            ->orderBy('id', 'desc');

        if ($request->filled('empleado_id')) {
            $query->where('empleado_id', $request->empleado_id);
        }

        // Búsqueda global (DataTable)
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->whereHas('empleado', function ($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%")
                  ->orWhere('numero_documento', 'like', "%{$search}%");
            });
        }

        $total    = $query->count();
        $start    = (int) $request->get('start', 0);
        $length   = (int) $request->get('length', 10);
        $rows     = $query->skip($start)->take($length)->get();

        $data = $rows->map(function ($liq) {
            $empleado = $liq->empleado;
            return [
                'id'             => $liq->id,
                'codigo'         => 'LIQ-' . str_pad($liq->id, 6, '0', STR_PAD_LEFT),
                'empleado_id'    => $liq->empleado_id,
                'colaborador'    => $empleado ? "{$empleado->nombres} {$empleado->apellidos}" : '-',
                'iniciales'      => $empleado
                    ? strtoupper(substr($empleado->nombres, 0, 1)) . strtoupper(substr($empleado->apellidos, 0, 1))
                    : '--',
                'ci'             => $empleado->numero_documento ?? '-',
                'periodo'        => str_pad($liq->periodo_mes, 2, '0', STR_PAD_LEFT) . '/' . $liq->periodo_anio,
                'salario_base'   => (int) $liq->salario_base,
                'total_ingresos' => (int) $liq->total_ingresos,
                'total_descuentos' => (int) $liq->total_descuentos,
                'total_ips'      => (int) $liq->total_aportes_ips,
                'total_neto'     => (int) $liq->total_neto,
                'estado'         => $liq->estado,
                'fecha_pago'     => $liq->fecha_pago?->format('d/m/Y') ?? '-',
            ];
        });

        return response()->json([
            'draw'            => (int) $request->get('draw', 1),
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $data,
        ]);
    }

    /**
     * Generar liquidación para UN empleado desde movimientos_nomina.
     */
    public function generar(Request $request, $empleadoId)
    {
        $request->validate([
            'anio' => 'required|integer',
            'mes'  => 'required|integer|min:1|max:12',
        ]);

        $anio      = (int) $request->anio;
        $mes       = (int) $request->mes;
        $empresaId = $request->get('empresa_id', 1);

        $empleado = Empleado::findOrFail($empleadoId);

        // Verificar si ya existe
        $existente = LiquidacionSalarial::where('empleado_id', $empleadoId)
            ->where('empresa_id', $empresaId)
            ->where('periodo_anio', $anio)
            ->where('periodo_mes', $mes)
            ->where('tipo', 'ordinaria')
            ->first();

        if ($existente) {
            return redirect()->route('liquidacion.show', $existente->id)
                ->with('info', 'Ya existe una liquidación para este período.');
        }

        DB::beginTransaction();
        try {
            // 1) Movimientos del período
            $movimientos = MovimientoNomina::where('empleado_id', $empleadoId)
                ->where('empresa_id', $empresaId)
                ->where('anio', $anio)
                ->where('mes', $mes)
                ->where('estado', 'activo')
                ->get();

            if ($movimientos->isEmpty()) {
                return redirect()->back()
                    ->with('error', 'No hay movimientos activos para este empleado en el período seleccionado.');
            }

            $totalIngresos   = $movimientos->where('es_ingreso', true)->sum('monto');
            $totalDescuentos = $movimientos->where('es_ingreso', false)->sum('monto');

            // 2) IPS
            $parametro     = ParametroIps::paraPeriodo($empresaId, $anio, $mes);
            $porcentajeIps = (float) $parametro->aporte_empleado;
            $aporteIps     = (int) round($totalIngresos * ($porcentajeIps / 100));

            $totalNeto = $totalIngresos - $totalDescuentos - $aporteIps;

            // 3) Cabecera
            $liq = LiquidacionSalarial::create([
                'empleado_id'       => $empleadoId,
                'empresa_id'        => $empresaId,
                'periodo_anio'      => $anio,
                'periodo_mes'       => $mes,
                'tipo'              => 'ordinaria',
                'salario_base'      => $empleado->salario_base,
                'total_ingresos'    => $totalIngresos,
                'total_descuentos'  => $totalDescuentos,
                'total_aportes_ips' => $aporteIps,
                'total_neto'        => $totalNeto,
                'estado'            => 'calculado',
                'fecha_calculo'     => now(),
                'creado_por'        => auth()->id(),
            ]);

            // 4) Detalle por cada movimiento
            $mapaConceptos = $this->mapaConceptos($empresaId);

            foreach ($movimientos as $mov) {
                LiquidacionSalarialDet::create([
                    'liquidacion_id'   => $liq->id,
                    'concepto_id'      => $mapaConceptos[$mov->tipo_movimiento] ?? 0,
                    'tipo'             => $mov->es_ingreso ? 'ingreso' : 'descuento',
                    'monto'            => $mov->monto,
                    'cantidad'         => 1,
                    'formula_aplicada' => strtoupper($mov->tipo_movimiento),
                ]);
            }

            // 5) Detalle del aporte IPS
            LiquidacionSalarialDet::create([
                'liquidacion_id'   => $liq->id,
                'concepto_id'      => $mapaConceptos['ips_empleado'] ?? 0,
                'tipo'             => 'aporte',
                'monto'            => $aporteIps,
                'cantidad'         => $porcentajeIps,
                'formula_aplicada' => 'IPS ' . $porcentajeIps . '%',
            ]);

            DB::commit();

            return redirect()->route('liquidacion.show', $liq->id)
                ->with('success', 'Liquidación generada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al generar liquidación: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Ver / imprimir la liquidación.
     */
    public function show($id)
    {
        $liquidacion = LiquidacionSalarial::with([
            'empleado',
            'empresa',
            'detalles.concepto',
        ])->findOrFail($id);

        return view('HR.liquidacion.recibo', compact('liquidacion'));
    }

    /**
     * Marcar como pagada.
     */
    public function pagar($id)
    {
        $liq = LiquidacionSalarial::findOrFail($id);
        $liq->update([
            'estado'          => 'pagado',
            'fecha_pago'      => now()->toDateString(),
            'actualizado_por' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Liquidación marcada como pagada.');
    }

    /**
     * Anular liquidación.
     */
    public function anular($id)
    {
        $liq = LiquidacionSalarial::findOrFail($id);
        $liq->update([
            'estado'          => 'anulado',
            'actualizado_por' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Liquidación anulada.');
    }

    /**
     * Mapa de tipo_movimiento → concepto_id
     */
    private function mapaConceptos(int $empresaId): array
    {
        $codigos = [
            'sueldo'         => 'SUE',
            'extra'          => 'EXT',
            'vale'           => 'VAL',
            'ausencia'       => 'AUS',
            'llegada_tardia' => 'LLA',
            'otros'          => 'OTR',
            'ips_empleado'   => 'IPS_EMP',
        ];

        $conceptos = ConceptoSalarial::where('empresa_id', $empresaId)
            ->whereIn('codigo', array_values($codigos))
            ->pluck('id', 'codigo');

        $mapa = [];
        foreach ($codigos as $key => $codigo) {
            $mapa[$key] = $conceptos[$codigo] ?? 0;
        }
        return $mapa;
    }
}