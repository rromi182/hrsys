<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cargo extends Model
{
    use SoftDeletes;
    protected $table = 'cargos';
    protected $fillable = ['empresa_id','departamento_id','nombre','codigo','descripcion','estado','creado_por','actualizado_por'];
    public function empresa() { return $this->belongsTo(Empresa::class); }
    public function departamento() { return $this->belongsTo(Departamento::class); }
}