<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departamento extends Model
{
    use SoftDeletes;
    protected $table = 'departamentos';
    protected $fillable = ['empresa_id','nombre','codigo','descripcion','encargado_id','estado','creado_por','actualizado_por'];
    public function empresa() { return $this->belongsTo(Empresa::class); }
}