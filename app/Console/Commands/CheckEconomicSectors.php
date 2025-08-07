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
        
        $sectors = EconomicSector::orderBy('id')->get(['id', 'name']);
        
        foreach ($sectors as $sector) {
            $this->line($sector->id . ' | ' . $sector->name);
        }
        
        $this->info('');
        $this->info('Total de sectores: ' . $sectors->count());
        $this->info('ID mínimo: ' . $sectors->min('id'));
        $this->info('ID máximo: ' . $sectors->max('id'));
    }
}