<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Matriz;
use App\Models\Traceability;
use Illuminate\Support\Facades\DB;

class UpdateMatrizTriedIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:matriz-tried-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar tried_id en registros de matriz que están en NULL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando actualización de tried_id en tabla matriz...');

        // Obtener todos los usuarios únicos que tienen registros en matriz
        $userIds = Matriz::distinct()->pluck('user_id');

        foreach ($userIds as $userId) {
            $this->info("Actualizando tried_id para usuario ID: {$userId}");

            // Obtener la ruta actual del usuario (tried más alto)
            $traceability = Traceability::where('user_id', $userId)
                ->orderBy('tried', 'desc')
                ->first();

            if ($traceability) {
                // Actualizar todos los registros de matriz para este usuario
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
