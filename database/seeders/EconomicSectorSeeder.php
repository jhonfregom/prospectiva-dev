<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EconomicSectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existen sectores económicos
        if (DB::table('economic_sectors')->count() > 0) {
            $this->command->info('Los sectores económicos ya existen, saltando seeder...');
            return;
        }

        $this->command->info('Creando sectores económicos...');

        $sectors = [
            ['name' => 'Agricultura, ganadería, caza, silvicultura y pesca', 'sort_order' => 1],
            ['name' => 'Explotación de minas y canteras', 'sort_order' => 2],
            ['name' => 'Industrias manufactureras', 'sort_order' => 3],
            ['name' => 'Suministro de electricidad, gas, vapor y aire acondicionado', 'sort_order' => 4],
            ['name' => 'Suministro de agua; gestión de residuos y saneamiento ambiental', 'sort_order' => 5],
            ['name' => 'Construcción', 'sort_order' => 6],
            ['name' => 'Comercio al por mayor y al por menor', 'sort_order' => 7],
            ['name' => 'Transporte y almacenamiento', 'sort_order' => 8],
            ['name' => 'Alojamiento y servicios de comida', 'sort_order' => 9],
            ['name' => 'Información y comunicaciones', 'sort_order' => 10],
            ['name' => 'Actividades financieras y de seguros', 'sort_order' => 11],
            ['name' => 'Actividades inmobiliarias', 'sort_order' => 12],
            ['name' => 'Actividades profesionales, científicas y técnicas', 'sort_order' => 13],
            ['name' => 'Actividades administrativas y de apoyo', 'sort_order' => 14],
            ['name' => 'Administración pública y defensa', 'sort_order' => 15],
            ['name' => 'Educación', 'sort_order' => 16],
            ['name' => 'Salud humana y asistencia social', 'sort_order' => 17],
            ['name' => 'Arte, entretenimiento y recreación', 'sort_order' => 18],
            ['name' => 'Otros servicios (organizaciones sociales, sindicatos, ONG, etc.)', 'sort_order' => 19],
            ['name' => 'Actividades de los hogares como empleadores', 'sort_order' => 20],
            ['name' => 'Organismos internacionales y otras instituciones extraterritoriales', 'sort_order' => 21]
        ];

        foreach ($sectors as $sector) {
            DB::table('economic_sectors')->insert([
                'name' => $sector['name'],
                'sort_order' => $sector['sort_order'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $this->command->info('Sectores económicos creados exitosamente: ' . count($sectors));
    }
}
