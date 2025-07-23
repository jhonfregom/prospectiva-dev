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
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:delete-data {user_id} {--force : Forzar eliminación sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina todos los registros relacionados con un usuario específico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $force = $this->option('force');

        // Verificar si el usuario existe
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

            // Contar registros antes de eliminar
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

            // Eliminar registros en orden (respetando claves foráneas)
            $this->info("Eliminando registros...");

            // 1. Eliminar conclusions (depende de hypothesis)
            $conclusionsDeleted = Conclusion::where('user_id', $userId)->delete();
            $this->info("  - Conclusions eliminados: {$conclusionsDeleted}");

            // 2. Eliminar scenarios (depende de hypothesis)
            $scenariosDeleted = Scenarios::where('user_id', $userId)->delete();
            $this->info("  - Scenarios eliminados: {$scenariosDeleted}");

            // 3. Eliminar hypothesis
            $hypothesisDeleted = Hypothesis::where('user_id', $userId)->delete();
            $this->info("  - Hypothesis eliminados: {$hypothesisDeleted}");

            // 4. Eliminar variables_map_analysis
            $variablesMapDeleted = VariableMapAnalisys::where('user_id', $userId)->delete();
            $this->info("  - Variables Map Analysis eliminados: {$variablesMapDeleted}");

            // 5. Eliminar matriz
            $matrizDeleted = Matriz::where('user_id', $userId)->delete();
            $this->info("  - Matriz eliminados: {$matrizDeleted}");

            // 6. Eliminar variables
            $variablesDeleted = Variable::where('user_id', $userId)->delete();
            $this->info("  - Variables eliminados: {$variablesDeleted}");

            // 7. Eliminar traceability
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
