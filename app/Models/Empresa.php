<?php
// app/Models/Empresa.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empresa extends Model
{
    use SoftDeletes;
    protected $table = 'empresas';
    protected $fillable = ['nombre','razon_social','ruc','direccion','telefono','correo','numero_patronal_mtess','logo','sitio_web','estado','creado_por','actualizado_por'];
}

