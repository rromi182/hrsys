<?php
// app/Services/GeneradorMovimientosNomina.php
namespace App\Services;

use App\Models\Empleado;
use App\Models\MovimientoNomina;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeneradorMovimientosNomina
{
    private const ESTADOS_ACTIVOS = ['activo', 'vacaciones', 'licencia'];

    public function generarMes(int $empresaId, int $anio, int $mes, ?int $usuarioId = null): array
    {
        $inicioMes = Carbon::create($anio, $mes, 1);
        $finMes    = $inicioMes->copy()->endOfMonth();
        // Fecha real de creación: HOY. Si generás para un mes pasado, usa el último día de ese mes.
        $fechaPago = Carbon::now()->isSameMonth($inicioMes)
            ? Carbon::now()->toDateString()
            : $finMes->toDateString();

        $empleados = Empleado::where('empresa_id', $empresaId)
            ->whereIn('estado', self::ESTADOS_ACTIVOS)
            ->where(function ($q) use ($finMes) {
                $q->whereNull('fecha_egreso')
                    ->orWhere('fecha_egreso', '>=', $finMes->toDateString());
            })
            ->where(function ($q) use ($inicioMes) {
                $q->whereNull('fecha_ingreso')
                    ->orWhere('fecha_ingreso', '<=', $inicioMes->copy()->endOfMonth()->toDateString());
            })
            ->get();

        $creados = 0;
        $omitidos = 0;

        foreach ($empleados as $empleado) {
            foreach (MovimientoNomina::TIPOS_INGRESO as $tipo) {
                // Chequeo previo (fuera de transacción, rápido)
                $yaExiste = MovimientoNomina::where('empleado_id', $empleado->id)
                    ->where('empresa_id', $empresaId)
                    ->where('anio', $anio)
                    ->where('mes', $mes)
                    ->where('tipo_movimiento', $tipo)
                    ->where('estado', 'activo')
                    ->exists();

                if ($yaExiste) {
                    $omitidos++;
                    continue;
                }

                try {
                    DB::transaction(function () use ($empleado, $empresaId, $anio, $mes, $fechaPago, $tipo, $usuarioId) {
                        // Doble chequeo dentro de la transacción con lock
                        $existe = MovimientoNomina::where('empleado_id', $empleado->id)
                            ->where('empresa_id', $empresaId)
                            ->where('anio', $anio)
                            ->where('mes', $mes)
                            ->where('tipo_movimiento', $tipo)
                            ->where('estado', 'activo')
                            ->lockForUpdate()
                            ->exists();

                        if ($existe) {
                            return false; // ya existe, otro proceso lo creó
                        }

                        MovimientoNomina::create([
                            'empleado_id'              => $empleado->id,
                            'empresa_id'               => $empresaId,
                            'fecha'                    => $fechaPago,
                            'tipo_movimiento'          => $tipo,
                            'monto'                    => MovimientoNomina::montoBasePara($empleado, $tipo),
                            'anio'                     => $anio,
                            'mes'                      => $mes,
                            'es_ingreso'               => true,
                            'generado_automaticamente' => true,
                            'observacion'              => 'Generado automáticamente',
                            'estado'                   => 'activo',
                            'creado_por'               => $usuarioId,
                        ]);

                        return true;
                    }) ? $creados++ : $omitidos++;
                } catch (QueryException $e) {
                    // Si tenés el índice único (Opción A), esto captura el duplicado
                    if ($e->getCode() === '23000') {
                        $omitidos++;
                    } else {
                        Log::error('Error generando movimiento', [
                            'empleado_id' => $empleado->id,
                            'tipo' => $tipo,
                            'error' => $e->getMessage(),
                        ]);
                        throw $e;
                    }
                }
            }
        }

        Log::info('Nómina generada', compact('empresaId', 'anio', 'mes', 'creados', 'omitidos'));

        return compact('creados', 'omitidos');
    }
}
