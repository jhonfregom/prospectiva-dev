<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Traceability;
use App\Models\Variable;
use App\Models\Hypothesis;
use App\Models\Conclusion;
use App\Models\Scenarios;
use App\Models\VariableMapAnalisys;

class UpdateTriedIds extends Command
{
    protected $signature = 'update:tried-ids {user_id?}';
    protected $description = 'Actualizar tried_id en registros existentes que están en NULL';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if ($userId) {
            $this->updateUserTriedIds($userId);
        } else {
            
            $users = \App\Models\User::all();
            foreach ($users as $user) {
                $this->updateUserTriedIds($user->id);
            }
        }
        
        $this->info('Actualización de tried_id completada.');
    }

    private function updateUserTriedIds($userId)
    {
        $this->info("Actualizando tried_id para usuario ID: {$userId}");

        $traceability = Traceability::getOrCreateForUser($userId);
        $traceabilityId = $traceability->id;
        
        $this->info("Traceability ID: {$traceabilityId}");

        $variablesCount = Variable::where('user_id', $userId)
            ->whereNull('tried_id')
            ->update(['tried_id' => $traceabilityId]);
        $this->info("Variables actualizadas: {$variablesCount}");

        $hypothesisCount = Hypothesis::where('user_id', $userId)
            ->whereNull('tried_id')
            ->update(['tried_id' => $traceabilityId]);
        $this->info("Hypothesis actualizadas: {$hypothesisCount}");

        $conclusionsCount = Conclusion::where('user_id', $userId)
            ->whereNull('tried_id')
            ->update(['tried_id' => $traceabilityId]);
        $this->info("Conclusions actualizadas: {$conclusionsCount}");

        $scenariosCount = Scenarios::where('user_id', $userId)
            ->whereNull('tried_id')
            ->update(['tried_id' => $traceabilityId]);
        $this->info("Scenarios actualizados: {$scenariosCount}");

        $analysisCount = VariableMapAnalisys::where('user_id', $userId)
            ->whereNull('tried_id')
            ->update(['tried_id' => $traceabilityId]);
        $this->info("Analysis actualizados: {$analysisCount}");
    }
}