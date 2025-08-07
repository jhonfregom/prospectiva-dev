<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('economic_sectors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        $sectors = [
            ['name' => 'Agricultura, ganadería, caza, silvicultura y pesca', 'sort_order' => 1],
            ['name' => 'Explotación de minas y canteras', 'sort_order' => 2],
            ['name' => 'Industrias manufactureras', 'sort_order' => 3],
            ['name' => 'Suministro de electricidad, gas, vapor y aire acondicionado', 'sort_order' => 4],
            ['name' => 'Distribución de agua; evacuación y tratamiento de aguas residuales, gestión de desechos y descontaminación', 'sort_order' => 5],
            ['name' => 'Construcción', 'sort_order' => 6],
            ['name' => 'Comercio al por mayor y al por menor; reparación de vehículos automotores y motocicletas', 'sort_order' => 7],
            ['name' => 'Transporte y almacenamiento', 'sort_order' => 8],
            ['name' => 'Alojamiento y servicios de comida', 'sort_order' => 9],
            ['name' => 'Información y comunicaciones', 'sort_order' => 10],
            ['name' => 'Actividades financieras y de seguros', 'sort_order' => 11],
            ['name' => 'Actividades inmobiliarias', 'sort_order' => 12],
            ['name' => 'Actividades profesionales, científicas y técnicas', 'sort_order' => 13],
            ['name' => 'Actividades de servicios administrativos y de apoyo', 'sort_order' => 14],
            ['name' => 'Administración pública y defensa; planes de seguridad social de afiliación obligatoria', 'sort_order' => 15],
            ['name' => 'Educación', 'sort_order' => 16],
            ['name' => 'Actividades de atención de la salud humana y de asistencia social', 'sort_order' => 17],
            ['name' => 'Actividades artísticas, de entretenimiento y recreativas', 'sort_order' => 18],
            ['name' => 'Otras actividades de servicios', 'sort_order' => 19],
            ['name' => 'Actividades de los hogares individuales en calidad de empleadores; actividades no diferenciadas de los hogares individuales como productores de bienes y servicios para uso propio', 'sort_order' => 20],
            ['name' => 'Actividades de organizaciones y entidades extraterritoriales', 'sort_order' => 21]
        ];

        foreach ($sectors as $sector) {
            DB::table('economic_sectors')->insert([
                'name' => $sector['name'],
                'sort_order' => $sector['sort_order'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('economic_sectors');
    }
};