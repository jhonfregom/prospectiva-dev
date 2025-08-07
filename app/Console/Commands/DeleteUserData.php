<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Variable;
use App\Models\Matriz;
use App\Models\VariableMapAnalisys;
use App\Models\Hypothesis;
use App\Models\Scenarios;
use App\Models\Conclusion;
use App\Models\Traceability;

class DeleteUserData extends Command
{
    
    protected $signature = 'user:delete-data {user_id} {--force : Forzar eliminación sin confirmación}';

    protected $description = 'Elimina todos los registros relacionados con un usuario específico';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $force = $this->option('force');

        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuario con ID {$userId} no encontrado.");
            return 1;
        }

        $this->info("Usuario encontrado: {$user->user} (ID: {$userId})");

        if (!$force) {
            if (!$this->confirm("¿Estás seguro de que quieres eliminar todos los datos del usuario {$user->user}? Esta acción no se puede deshacer.")) {
                $this->info("Operación cancelada.");
                return 0;
            }
        }

        $this->info("Iniciando eliminación de datos para el usuario {$user->user}...");

        try {
            DB::beginTransaction();

            $counts = [
                'variables' => Variable::where('user_id', $userId)->count(),
                'matriz' => Matriz::where('user_id', $userId)->count(),
                'variables_map_analysis' => VariableMapAnalisys::where('user_id', $userId)->count(),
                'hypothesis' => Hypothesis::where('user_id', $userId)->count(),
                'scenarios' => Scenarios::where('user_id', $userId)->count(),
                'conclusions' => Conclusion::where('user_id', $userId)->count(),
                'traceability' => Traceability::where('user_id', $userId)->count(),
            ];

            $this->info("Registros encontrados:");
            foreach ($counts as $table => $count) {
                $this->line("  - {$table}: {$count} registros");
            }

            $this->info("Eliminando registros...");

            $conclusionsDeleted = Conclusion::where('user_id', $userId)->delete();
            $this->info("  - Conclusions eliminados: {$conclusionsDeleted}");

            $scenariosDeleted = Scenarios::where('user_id', $userId)->delete();
            $this->info("  - Scenarios eliminados: {$scenariosDeleted}");

            $hypothesisDeleted = Hypothesis::where('user_id', $userId)->delete();
            $this->info("  - Hypothesis eliminados: {$hypothesisDeleted}");

            $variablesMapDeleted = VariableMapAnalisys::where('user_id', $userId)->delete();
            $this->info("  - Variables Map Analysis eliminados: {$variablesMapDeleted}");

            $matrizDeleted = Matriz::where('user_id', $userId)->delete();
            $this->info("  - Matriz eliminados: {$matrizDeleted}");

            $variablesDeleted = Variable::where('user_id', $userId)->delete();
            $this->info("  - Variables eliminados: {$variablesDeleted}");

            $traceabilityDeleted = Traceability::where('user_id', $userId)->delete();
            $this->info("  - Traceability eliminados: {$traceabilityDeleted}");

            DB::commit();

            $this->info("✅ Eliminación completada exitosamente.");
            $this->info("Total de registros eliminados:");
            $this->line("  - Conclusions: {$conclusionsDeleted}");
            $this->line("  - Scenarios: {$scenariosDeleted}");
            $this->line("  - Hypothesis: {$hypothesisDeleted}");
            $this->line("  - Variables Map Analysis: {$variablesMapDeleted}");
            $this->line("  - Matriz: {$matrizDeleted}");
            $this->line("  - Variables: {$variablesDeleted}");
            $this->line("  - Traceability: {$traceabilityDeleted}");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error durante la eliminación: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}