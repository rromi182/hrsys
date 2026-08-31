<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class Sucursal extends Model
{
    protected $table = 'sucursales';
    protected $fillable = ['empresa_id','nombre','codigo','direccion','telefono','correo','encargado_id','estado','creado_por','actualizado_por'];
    public function empresa() { return $this->belongsTo(Empresa::class); }
}