<?php
// app/Models/ConceptoSalarial.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConceptoSalarial extends Model
{
    use SoftDeletes;

    protected $table = 'conceptos_salariales';

    // Tu tabla usa creado_en / actualizado_en / eliminado_en
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    const DELETED_AT = 'eliminado_en';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'codigo',
        'tipo',            // ingreso | descuento | aporte
        'categoria',       // basico | extra | bono | comision | viatico | descuento_legal | descuento_voluntario
        'formula',
        'es_ips',
        'es_gravado',
        'valor_defecto',
        'estado',
        'creado_por',
        'actualizado_por',
    ];

    protected $casts = [
        'es_ips'        => 'boolean',
        'es_gravado'    => 'boolean',
        'estado'        => 'boolean',
        'valor_defecto' => 'decimal:2',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function detalles()
    {
        return $this->hasMany(LiquidacionSalarialDet::class, 'concepto_id');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }

    public function scopeEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    // Helpers
    public function getTipoBadgeAttribute(): string
    {
        return match ($this->tipo) {
            'ingreso'   => '<span class="badge bg-success-subtle text-success">Ingreso</span>',
            'descuento' => '<span class="badge bg-danger-subtle text-danger">Descuento</span>',
            'aporte'    => '<span class="badge bg-warning-subtle text-warning">Aporte</span>',
            default     => '<span class="badge bg-secondary-subtle text-secondary">' . $this->tipo . '</span>',
        };
    }
}