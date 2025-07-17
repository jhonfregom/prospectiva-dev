<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Variable;

class InitializeVariableCounters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'variables:initialize-counters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize edits_variable and edits_now_condition counters for existing variables';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Inicializando contadores de ediciones para variables existentes...');

        // Obtener todas las variables que no tienen edits_variable inicializado
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