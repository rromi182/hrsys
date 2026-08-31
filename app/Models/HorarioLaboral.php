<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioLaboral extends Model
{
    protected $table = 'horarios_laborales';
    protected $fillable = ['empresa_id','nombre','codigo','tipo','lunes_entrada','lunes_salida','martes_entrada','martes_salida','miercoles_entrada','miercoles_salida','jueves_entrada','jueves_salida','viernes_entrada','viernes_salida','sabado_entrada','sabado_salida','domingo_entrada','domingo_salida','duracion_pausa','estado','creado_por','actualizado_por'];
}