<?php
// app/Models/MovimientoNomina.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoNomina extends Model
{
    use HasFactory;

    protected $table = 'movimientos_nomina';

    // === CONSTANTES DE MONTOS PREDETERMINADOS ===
    const MONTO_SUELDO = 3044000;
    const MONTO_EXTRA = 500000;
    const MONTO_VALE = 0;
    const MONTO_AUSENCIA = 0;
    const MONTO_LLEGADA_TARDIA = 0;
    const MONTO_OTROS = 0;

    protected $fillable = [
        'empleado_id',
        'empresa_id',
        'fecha',
        'tipo_movimiento',
        'monto',
        'observacion',
        'anio',
        'mes',
        'es_ingreso',
        'estado',
        'creado_por',
        'actualizado_por',
    ];

    protected $casts = [
        'fecha' => 'date',
        'es_ingreso' => 'boolean',
        'monto' => 'integer',
        'anio' => 'integer',
        'mes' => 'integer',
    ];

    // Relaciones
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopePeriodo($query, $anio, $mes)
    {
        return $query->where('anio', $anio)->where('mes', $mes);
    }

    public function scopeEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    // Helpers
    public static function determinarNaturaleza(string $tipo): bool
    {
        $ingresos = ['sueldo', 'extra'];
        return in_array($tipo, $ingresos);
    }

    public function getMontoAjustadoAttribute(): int
    {
        return $this->es_ingreso ? $this->monto : -$this->monto;
    }

    /**
     * Obtener el monto predeterminado para un tipo de movimiento
     */
    public static function getMontoPredeterminado(string $tipo): int
    {
        return match ($tipo) {
            'sueldo' => self::MONTO_SUELDO,
            'extra' => self::MONTO_EXTRA,
            'vale' => self::MONTO_VALE,
            'ausencia' => self::MONTO_AUSENCIA,
            'llegada_tardia' => self::MONTO_LLEGADA_TARDIA,
            'otros' => self::MONTO_OTROS,
            default => 0,
        };
    }

    /**
     * Obtener todos los tipos de movimiento con sus montos predeterminados
     */
    public static function getTiposConMontos(): array
    {
        return [
            'sueldo' => [
                'label' => 'Sueldo',
                'monto' => self::MONTO_SUELDO,
                'es_ingreso' => true,
            ],
            'extra' => [
                'label' => 'Extra',
                'monto' => self::MONTO_EXTRA,
                'es_ingreso' => true,
            ],
            'vale' => [
                'label' => 'Vale',
                'monto' => self::MONTO_VALE,
                'es_ingreso' => false,
            ],
            'ausencia' => [
                'label' => 'Ausencia',
                'monto' => self::MONTO_AUSENCIA,
                'es_ingreso' => false,
            ],
            'llegada_tardia' => [
                'label' => 'Llegada Tardía',
                'monto' => self::MONTO_LLEGADA_TARDIA,
                'es_ingreso' => false,
            ],
            'otros' => [
                'label' => 'Otros',
                'monto' => self::MONTO_OTROS,
                'es_ingreso' => false,
            ],
        ];
    }
}