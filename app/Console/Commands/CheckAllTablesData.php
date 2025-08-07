<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Variable;
use App\Models\Matriz;
use App\Models\Hypothesis;
use App\Models\Scenarios;
use App\Models\Conclusion;
use Illuminate\Support\Facades\DB;

class CheckAllTablesData extends Command
{
    protected $signature = 'check:all-tables-data';
    protected $description = 'Verificar datos en todas las tablas por usuario';

    public function handle()
    {
        $this->info('🔍 Verificando datos en todas las tablas...');
        
        $users = User::all();
        
        foreach ($users as $user) {
            $this->info("\n👤 Usuario: {$user->first_name} {$user->last_name} (ID: {$user->id})");

            $variables = Variable::where('user_id', $user->id)->count();
            $this->line("   📊 Variables: {$variables}");
            if ($variables > 0) {
                $varDetails = Variable::where('user_id', $user->id)->select('id', 'name_variable', 'state')->get();
                foreach ($varDetails as $var) {
                    $this->line("      - ID: {$var->id}, Nombre: {$var->name_variable}, Estado: {$var->state}");
                }
            }

            $matriz = Matriz::where('user_id', $user->id)->count();
            $this->line("   📋 Matriz: {$matriz}");
            if ($matriz > 0) {
                $matDetails = Matriz::where('user_id', $user->id)->select('id', 'id_matriz', 'id_variable', 'state')->get();
                foreach ($matDetails as $mat) {
                    $this->line("      - ID: {$mat->id}, ID Matriz: {$mat->id_matriz}, ID Variable: {$mat->id_variable}, Estado: {$mat->state}");
                }
            }

            $hypothesis = Hypothesis::where('user_id', $user->id)->count();
            $this->line("   🔬 Hipótesis: {$hypothesis}");

            $scenarios = Scenarios::where('user_id', $user->id)->count();
            $this->line("   🎯 Escenarios: {$scenarios}");

            $conclusions = Conclusion::where('user_id', $user->id)->count();
            $this->line("   📝 Conclusiones: {$conclusions}");
        }

        $this->info("\n📈 Totales por tabla:");
        $this->line("   Variables: " . Variable::count());
        $this->line("   Matriz: " . Matriz::count());
        $this->line("   Hipótesis: " . Hypothesis::count());
        $this->line("   Escenarios: " . Scenarios::count());
        $this->line("   Conclusiones: " . Conclusion::count());
        
        $this->info("\n✅ Verificación completada");
    }
}