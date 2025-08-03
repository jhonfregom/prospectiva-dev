<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EconomicSector;

class CheckEconomicSectors extends Command
{
    protected $signature = 'economic-sectors:check';
    protected $description = 'Verificar que los sectores económicos se cargaron correctamente';

    public function handle()
    {
        $this->info('🔍 Verificando sectores económicos...');
        
        try {
            $sectors = EconomicSector::active()->ordered()->get();
            
            if ($sectors->count() > 0) {
                $this->info("✅ Se encontraron {$sectors->count()} sectores económicos:");
                
                foreach ($sectors as $sector) {
                    $this->line("   ID: {$sector->id} - {$sector->name}");
                }
                
                $this->info('');
                $this->info('🎯 API endpoint disponible en: /economic-sectors');
                
            } else {
                $this->warn('⚠️  No se encontraron sectores económicos');
                $this->info('Ejecuta: php artisan migrate para crear la tabla');
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error al verificar sectores económicos:');
            $this->line("   {$e->getMessage()}");
        }
        
        return 0;
    }
} 