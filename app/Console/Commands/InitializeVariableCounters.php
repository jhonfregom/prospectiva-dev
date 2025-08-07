<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Variable;

class InitializeVariableCounters extends Command
{
    
    protected $signature = 'variables:initialize-counters';

    protected $description = 'Initialize edits_variable and edits_now_condition counters for existing variables';

    public function handle()
    {
        $this->info('Inicializando contadores de ediciones para variables existentes...');

        $variables = Variable::whereNull('edits_variable')
            ->orWhere('edits_variable', 0)
            ->get();

        $updatedCount = 0;

        foreach ($variables as $variable) {
            $variable->edits_variable = 0;
            $variable->edits_now_condition = 0;
            $variable->save();
            $updatedCount++;
        }

        $this->info("Se actualizaron {$updatedCount} variables.");
        $this->info('¡Contadores inicializados correctamente!');

        return 0;
    }
}