<?php
// database/seeders/ConceptoSalarialSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConceptoSalarialSeeder extends Seeder
{
    public function run(): void
    {
        $empresaId = 1;
        $conceptos = [
            ['SUE','Sueldo Básico','ingreso','basico',1,1, 3044000],
            ['EXT','Horas Extras','ingreso','extra',1,1, 500000],
            ['BON','Bono','ingreso','bono',1,1, 0],
            ['COM','Comisión','ingreso','comision',1,1, 0],
            ['VIA','Viático','ingreso','viatico',0,0, 0],
            ['VAL','Vale','descuento','descuento_voluntario',0,0, 0],
            ['AUS','Ausencia','descuento','descuento_legal',0,0, 0],
            ['LLA','Llegada Tardía','descuento','descuento_legal',0,0, 0],
            ['OTR','Otros Descuentos','descuento','descuento_voluntario',0,0, 0],
            ['IPS_EMP','Aporte IPS Empleado','aporte','descuento_legal',1,0, 0],
            ['IPS_PAT','Aporte IPS Empleador','aporte','descuento_legal',0,0, 0],
        ];

        foreach ($conceptos as $c) {
            DB::table('conceptos_salariales')->updateOrInsert(
                ['empresa_id' => $empresaId, 'codigo' => $c[0]],
                [
                    'nombre'        => $c[1],
                    'tipo'          => $c[2],
                    'categoria'     => $c[3],
                    'es_ips'        => $c[4],
                    'es_gravado'    => $c[5],
                    'valor_defecto' => $c[6],
                    'estado'        => 1,
                    'creado_en'     => now(),
                    'actualizado_en'=> now(),
                    'creado_por'    => 1,
                ]
            );
        }
    }
}