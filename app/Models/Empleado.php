<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empleado extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'empleados';
    // No hace falta poner $connection porque usa 'mysql' (laravel) por defecto

    protected $fillable = [
        'nombres',
        'apellidos',
        'tipo_documento',
        'numero_documento',
        'fecha_nacimiento',
        'sexo',
        'estado_civil',
        'nacionalidad',
        'direccion',
        'departamento_residencia',
        'ciudad_residencia',
        'telefono',
        'correo',
        'foto',
        'empresa_id',
        'sucursal_id',
        'departamento_id',
        'cargo_id',
        'codigo_empleado',
        'tipo_contrato_id',
        'horario_id',
        'fecha_ingreso',
        'fecha_egreso',
        'estado',
        'jefe_inmediato_id',
        'salario_base',
        'numero_ips',
        'numero_ips',
        'afiliado_ips',
        'fecha_afiliacion_ips',
        'prueba_finalizada',
        'profesion',
        'estado',
        'creado_por',
        'actualizado_por'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ingreso'    => 'date',
        'fecha_egreso'     => 'date',
        'afiliado_ips'         => 'boolean',
        'fecha_afiliacion_ips' => 'date',
        'prueba_finalizada'    => 'boolean',
    ];

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    /* Relación lógica con user (tabla en otra BD) */
    public function user()
    {
        return $this->hasOne(User::class, 'empleado_id');
    }

    /* Relaciones locales (misma BD laravel) */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }

    /* Empleados activos que todavía no tienen usuario en hrsys */
    public function scopeSinUsuario($query)
    {
        $usados = \DB::connection('legacy')
            ->table('users')
            ->whereNotNull('empleado_id')
            ->pluck('empleado_id');

        return $query->whereNotIn('id', $usados)
            ->where('estado', 'activo');
    }

    public function tipoContrato()
    {
        return $this->belongsTo(TipoContrato::class, 'tipo_contrato_id');
    }

    public function horario()
    {
        return $this->belongsTo(HorarioLaboral::class, 'horario_id');
    }

    /** ¿Se le debe calcular/descontar IPS en la liquidación? */
    public function debeAplicarIps(): bool
    {
        return (bool) $this->afiliado_ips;
    }

    /** ¿Ya pasó el período de prueba y corresponde generar el extra? */
    public function debeGenerarExtra(): bool
    {
        return (bool) $this->prueba_finalizada;
    }
}
