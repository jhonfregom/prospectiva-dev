<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Matriz;
use App\Models\Traceability;
use Illuminate\Support\Facades\DB;

class UpdateMatrizTriedIds extends Command
{
    
    protected $signature = 'update:matriz-tried-ids';

    protected $description = 'Actualizar tried_id en registros de matriz que están en NULL';

    public function handle()
    {
        $this->info('Iniciando actualización de tried_id en tabla matriz...');

        $userIds = Matriz::distinct()->pluck('user_id');

        foreach ($userIds as $userId) {
            $this->info("Actualizando tried_id para usuario ID: {$userId}");

            $traceability = Traceability::where('user_id', $userId)
                ->orderBy('tried', 'desc')
                ->first();

            if ($traceability) {
                
                $updated = Matriz::where('user_id', $userId)
                    ->whereNull('tried_id')
                    ->update(['tried_id' => $traceability->id]);

                $this->info("Actualizados {$updated} registros de matriz para usuario {$userId}");
            } else {
                $this->warn("No se encontró traceability para usuario {$userId}");
            }
        }

        $this->info('Actualización de tried_id en matriz completada.');
    }
}