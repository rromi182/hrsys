<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoContrato extends Model
{
    protected $table = 'tipos_contrato';
    protected $fillable = ['empresa_id','nombre','codigo','descripcion','duracion_defecto','es_indefinido','creado_por','actualizado_por'];
}