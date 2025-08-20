<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Actualizar los nombres de los sectores económicos según la lista correcta
        $sectorUpdates = [
            // Sector 5
            'Distribución de agua; evacuación y tratamiento de aguas residuales, gestión de desechos y descontaminación' => 'Suministro de agua; gestión de residuos y saneamiento ambiental',
            
            // Sector 7
            'Comercio al por mayor y al por menor; reparación de vehículos automotores y motocicletas' => 'Comercio al por mayor y al por menor',
            
            // Sector 14
            'Actividades de servicios administrativos y de apoyo' => 'Actividades administrativas y de apoyo',
            
            // Sector 15
            'Administración pública y defensa; planes de seguridad social de afiliación obligatoria' => 'Administración pública y defensa',
            
            // Sector 17
            'Actividades de atención de la salud humana y de asistencia social' => 'Salud humana y asistencia social',
            
            // Sector 18
            'Actividades artísticas, de entretenimiento y recreativas' => 'Arte, entretenimiento y recreación',
            
            // Sector 19
            'Otras actividades de servicios' => 'Otros servicios (organizaciones sociales, sindicatos, ONG, etc.)',
            
            // Sector 20
            'Actividades de los hogares individuales en calidad de empleadores; actividades no diferenciadas de los hogares individuales como productores de bienes y servicios para uso propio' => 'Actividades de los hogares como empleadores',
            
            // Sector 21
            'Actividades de organizaciones y entidades extraterritoriales' => 'Organismos internacionales y otras instituciones extraterritoriales'
        ];

        foreach ($sectorUpdates as $oldName => $newName) {
            DB::table('economic_sectors')
                ->where('name', $oldName)
                ->update(['name' => $newName]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir los cambios si es necesario
        $sectorReverts = [
            'Suministro de agua; gestión de residuos y saneamiento ambiental' => 'Distribución de agua; evacuación y tratamiento de aguas residuales, gestión de desechos y descontaminación',
            'Comercio al por mayor y al por menor' => 'Comercio al por mayor y al por menor; reparación de vehículos automotores y motocicletas',
            'Actividades administrativas y de apoyo' => 'Actividades de servicios administrativos y de apoyo',
            'Administración pública y defensa' => 'Administración pública y defensa; planes de seguridad social de afiliación obligatoria',
            'Salud humana y asistencia social' => 'Actividades de atención de la salud humana y de asistencia social',
            'Arte, entretenimiento y recreación' => 'Actividades artísticas, de entretenimiento y recreativas',
            'Otros servicios (organizaciones sociales, sindicatos, ONG, etc.)' => 'Otras actividades de servicios',
            'Actividades de los hogares como empleadores' => 'Actividades de los hogares individuales en calidad de empleadores; actividades no diferenciadas de los hogares individuales como productores de bienes y servicios para uso propio',
            'Organismos internacionales y otras instituciones extraterritoriales' => 'Actividades de organizaciones y entidades extraterritoriales'
        ];

        foreach ($sectorReverts as $newName => $oldName) {
            DB::table('economic_sectors')
                ->where('name', $newName)
                ->update(['name' => $oldName]);
        }
    }
};
