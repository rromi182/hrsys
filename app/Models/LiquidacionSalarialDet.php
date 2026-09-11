<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiquidacionSalarialDet extends Model
{
    protected $table = 'liquidaciones_salariales_det';
    public $timestamps = false;

    protected $fillable = [
        'liquidacion_id', 'concepto_id', 'tipo',
        'monto', 'cantidad', 'formula_aplicada',
    ];

    protected $casts = [
        'monto'    => 'integer',
        'cantidad' => 'float',
    ];

    public function liquidacion()
    {
        return $this->belongsTo(LiquidacionSalarial::class, 'liquidacion_id');
    }

    public function concepto()
    {
        return $this->belongsTo(ConceptoSalarial::class, 'concepto_id');
    }
}