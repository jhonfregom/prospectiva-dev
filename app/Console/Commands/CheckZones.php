<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Zones;

class CheckZones extends Command
{
    protected $signature = 'check:zones';
    protected $description = 'Verificar las zonas existentes';

    public function handle()
    {
        $this->info('🗺️ Verificando zonas existentes...');
        
        $zones = Zones::all();
        $this->info("Total de zonas: " . $zones->count());
        
        if ($zones->count() > 0) {
            foreach ($zones as $zone) {
                $this->line("   ID: {$zone->id}, Nombre: {$zone->name_zones}");
            }
        } else {
            $this->warn("   No hay zonas creadas");
        }
        
        $this->info("\n✅ Verificación completada");
    }
} 