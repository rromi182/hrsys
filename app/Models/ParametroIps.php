<?php
// app/Models/ParametroIps.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParametroIps extends Model
{
    protected $table = 'parametros_ips';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'empresa_id',
        'anio',
        'mes',
        'aporte_empleado',
        'aporte_empleador',
        'salario_minimo',
        'aporte_fondo_pension',
        'aporte_seguro_salud',
        'creado_por',
        'actualizado_por',
    ];

    protected $casts = [
        'anio'                => 'integer',
        'mes'                 => 'integer',
        'aporte_empleado'     => 'decimal:2',
        'aporte_empleador'    => 'decimal:2',
        'salario_minimo'      => 'integer',
        'aporte_fondo_pension'=> 'integer',
        'aporte_seguro_salud' => 'integer',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Obtener el parámetro de IPS para un período. Si no existe,
     * devuelve valores por defecto (Paraguay 2026).
     */
    public static function paraPeriodo(int $empresaId, int $anio, int $mes): self
    {
        return self::firstOrNew(
            ['empresa_id' => $empresaId, 'anio' => $anio, 'mes' => $mes],
            [
                'aporte_empleado'  => 9.00,
                'aporte_empleador' => 16.50,
                'salario_minimo'   => 3044000,
            ]
        );
    }
}