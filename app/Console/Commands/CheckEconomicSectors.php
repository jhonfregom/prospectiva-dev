<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EconomicSector;

class CheckEconomicSectors extends Command
{
    
    protected $signature = 'check:economic-sectors';

    protected $description = 'Verificar los IDs de los sectores económicos';

    public function handle()
    {
        $this->info('Sectores económicos en la base de datos:');
        $this->info('ID | Nombre');
        $this->info('---|-------');
        
        $sectors = EconomicSector::orderBy('sort_order')->get(['id', 'name', 'sort_order']);
        
        foreach ($sectors as $sector) {
            $this->line($sector->id . ' | ' . $sector->name . ' (orden: ' . $sector->sort_order . ')');
        }
        
        $this->info('');
        $this->info('Total de sectores: ' . $sectors->count());
        
        // Lista esperada de 21 sectores
        $expectedSectors = [
            'Agricultura, ganadería, caza, silvicultura y pesca',
            'Explotación de minas y canteras',
            'Industrias manufactureras',
            'Suministro de electricidad, gas, vapor y aire acondicionado',
            'Suministro de agua; gestión de residuos y saneamiento ambiental',
            'Construcción',
            'Comercio al por mayor y al por menor',
            'Transporte y almacenamiento',
            'Alojamiento y servicios de comida',
            'Información y comunicaciones',
            'Actividades financieras y de seguros',
            'Actividades inmobiliarias',
            'Actividades profesionales, científicas y técnicas',
            'Actividades administrativas y de apoyo',
            'Administración pública y defensa',
            'Educación',
            'Salud humana y asistencia social',
            'Arte, entretenimiento y recreación',
            'Otros servicios (organizaciones sociales, sindicatos, ONG, etc.)',
            'Actividades de los hogares como empleadores',
            'Organismos internacionales y otras instituciones extraterritoriales'
        ];
        
        $currentSectors = $sectors->pluck('name')->toArray();
        
        $this->info('');
        $this->info('Verificando sectores faltantes:');
        $missingSectors = [];
        
        foreach ($expectedSectors as $index => $expectedSector) {
            if (!in_array($expectedSector, $currentSectors)) {
                $missingSectors[] = ($index + 1) . '. ' . $expectedSector;
            }
        }
        
        if (empty($missingSectors)) {
            $this->info('✅ Todos los sectores están presentes');
        } else {
            $this->warn('❌ Sectores faltantes:');
            foreach ($missingSectors as $missing) {
                $this->line('   ' . $missing);
            }
        }
        
        $this->info('');
        $this->info('Verificando sectores extra:');
        $extraSectors = [];
        
        foreach ($currentSectors as $currentSector) {
            if (!in_array($currentSector, $expectedSectors)) {
                $extraSectors[] = $currentSector;
            }
        }
        
        if (empty($extraSectors)) {
            $this->info('✅ No hay sectores extra');
        } else {
            $this->warn('❌ Sectores extra:');
            foreach ($extraSectors as $extra) {
                $this->line('   ' . $extra);
            }
        }
    }
}