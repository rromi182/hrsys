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
}
