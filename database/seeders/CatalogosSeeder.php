<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogosSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        DB::table('regiones')->insert([
            ['numero' => 'I',   'nombre' => 'Región de Tarapacá', 'capital' => 'Iquique','created_at' => $now, 'updated_at' => $now],
            ['numero' => 'II',  'nombre' => 'Región de Antofagasta', 'capital' => 'Antofagasta','created_at' => $now, 'updated_at' => $now],
            ['numero' => 'III', 'nombre' => 'Región de Atacama', 'capital' => 'Copiapó','created_at' => $now, 'updated_at' => $now],
            ['numero' => 'IV',  'nombre' => 'Región de Coquimbo', 'capital' => 'La Serena','created_at' => $now, 'updated_at' => $now],
            ['numero' => 'V',   'nombre' => 'Región de Valparaíso', 'capital' => 'Valparaíso','created_at' => $now, 'updated_at' => $now],
            ['numero' => 'VI',  'nombre' => 'Región del Libertador General Bernardo O\'Higgins', 'capital' => 'Rancagua','created_at' => $now, 'updated_at' => $now],
            ['numero' => 'VII', 'nombre' => 'Región del Maule', 'capital' => 'Talca','created_at' => $now, 'updated_at' => $now],
            ['numero' => 'VIII','nombre' => 'Región del Biobío', 'capital' => 'Concepción','created_at' => $now, 'updated_at' => $now],
            ['numero' => 'IX',  'nombre' => 'Región de La Araucanía', 'capital' => 'Temuco','created_at' => $now, 'updated_at' => $now],
            ['numero' => 'X',   'nombre' => 'Región de Los Lagos', 'capital' => 'Puerto Montt','created_at' => $now, 'updated_at' => $now],
            ['numero' => 'XI',  'nombre' => 'Región de Aysén', 'capital' => 'Coyhaique','created_at' => $now, 'updated_at' => $now],
            ['numero' => 'XII', 'nombre' => 'Región de Magallanes y Antártica Chilena', 'capital' => 'Punta Arenas','created_at' => $now, 'updated_at' => $now],
            ['numero' => 'RM',  'nombre' => 'Región Metropolitana de Santiago', 'capital' => 'Santiago','created_at' => $now, 'updated_at' => $now],
            ['numero' => 'XIV', 'nombre' => 'Región de Los Ríos', 'capital' => 'Valdivia','created_at' => $now, 'updated_at' => $now],
            ['numero' => 'XV',  'nombre' => 'Región de Arica y Parinacota', 'capital' => 'Arica','created_at' => $now, 'updated_at' => $now],
            ['numero' => 'XVI', 'nombre' => 'Región de Ñuble', 'capital' => 'Chillán','created_at' => $now, 'updated_at' => $now],
            ['numero' => 'SC',  'nombre' => 'Sede Central', 'capital' => 'Santiago','created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('tipo_equipos')->insert([
            ['nombre' => 'Computador','created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Notebook','created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Monitor','created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Proyector','created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Impresora','created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Tablet','created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Plotter','created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Otro','created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('tipo_discos')->insert([
            ['nombre' => 'HDD','created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'SSD','created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'SSD NVMe','created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Sin Disco','created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('estados_equipo')->insert([
            ['nombre' => 'Correcto', 'color' => 'success','created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Observación', 'color' => 'warning','created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Proceso de Baja', 'color' => 'info','created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'De Baja', 'color' => 'danger','created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('tipo_equipos')->insert([]); // placeholder, ignore

        DB::table('detalle_equipos'); // no-op, just to ensure table exists before next steps
    }
}