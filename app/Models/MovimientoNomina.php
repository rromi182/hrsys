<?php
// app/Models/MovimientoNomina.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoNomina extends Model
{
    use HasFactory;

    protected $table = 'movimientos_nomina';

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

    /**
     * Scope para filtrar por estado activo
     */
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Scope para filtrar por período (año y mes)
     */
    public function scopePeriodo($query, $anio, $mes)
    {
        return $query->where('anio', $anio)->where('mes', $mes);
    }

    /**
     * Scope para filtrar por empresa
     */
    public function scopeEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    /**
     * Determina si el tipo de movimiento es ingreso (+) o descuento (-)
     */
    public static function determinarNaturaleza(string $tipo): bool
    {
        $ingresos = ['sueldo', 'extra'];
        return in_array($tipo, $ingresos);
    }

    /**
     * Monto ajustado según naturaleza (positivo o negativo para cálculos)
     */
    public function getMontoAjustadoAttribute(): int
    {
        return $this->es_ingreso ? $this->monto : -$this->monto;
    }

    /**
     * Badge class para naturaleza
     */
    public function getNaturalezaBadgeAttribute(): string
    {
        return $this->es_ingreso
            ? '<span class="badge bg-success-subtle text-success">Ingreso</span>'
            : '<span class="badge bg-danger-subtle text-danger">Descuento</span>';
    }

    /**
     * Badge class para estado
     */
    public function getEstadoBadgeAttribute(): string
    {
        $badges = [
            'activo'   => '<span class="badge bg-primary-subtle text-primary">Activo</span>',
            'anulado'  => '<span class="badge bg-secondary-subtle text-secondary">Anulado</span>',
        ];
        return $badges[$this->estado] ?? $badges['activo'];
    }
}