<?php
//
namespace App\Models; 

use Illuminate\Database\Eloquent\Model;

class LiquidacionSalarial extends Model
{
    protected $table = 'liquidaciones_salariales';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'empleado_id', 'empresa_id',
        'periodo_anio', 'periodo_mes', 'tipo',
        'salario_base', 'total_ingresos', 'total_descuentos',
        'total_aportes_ips', 'total_neto',
        'estado', 'fecha_calculo', 'fecha_aprobacion', 'fecha_pago',
        'observaciones', 'creado_por', 'actualizado_por',
    ];

    protected $casts = [
        'fecha_calculo'    => 'datetime',
        'fecha_aprobacion' => 'datetime',
        'fecha_pago'       => 'date',
        'salario_base'     => 'integer',
        'total_ingresos'   => 'integer',
        'total_descuentos' => 'integer',
        'total_aportes_ips'=> 'integer',
        'total_neto'       => 'integer',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function detalles()
    {
        return $this->hasMany(LiquidacionSalarialDet::class, 'liquidacion_id');
    }
}