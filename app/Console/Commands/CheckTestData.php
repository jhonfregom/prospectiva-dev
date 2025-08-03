<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Variable;
use App\Models\Traceability;
use App\Models\Note;
use App\Models\Matriz;
use App\Models\Hypothesis;
use App\Models\Scenarios;
use App\Models\Conclusion;

class CheckTestData extends Command
{
    protected $signature = 'test:check-data';
    protected $description = 'Verificar que los datos de prueba se hayan creado correctamente';

    public function handle()
    {
        $this->info('🔍 Verificando datos de prueba...');
        
        // Verificar usuarios
        $this->info("\n👥 Usuarios creados:");
        $users = User::whereIn('id', [1, 2, 3, 4])->get();
        foreach ($users as $user) {
            $this->line("   ID: {$user->id} - Usuario: {$user->user} - Tipo: {$user->registration_type}");
            $this->line("      Nombre: {$user->names} {$user->surnames}");
            $this->line("      User: {$user->user}");
            if ($user->company_name) {
                $this->line("      Empresa: {$user->company_name} (NIT: {$user->nit})");
            }
            $this->line("      Ciudad: {$user->city} - Sector: {$user->economic_sector}");
            $this->line("");
        }
        
        // Verificar variables
        $this->info("📊 Variables creadas:");
        $variables = Variable::whereIn('user_id', [1, 2, 3, 4])->get();
        $this->line("   Total: {$variables->count()} variables");
        foreach ($variables as $variable) {
            $this->line("   - {$variable->name_variable} (Usuario: {$variable->user_id})");
        }
        
        // Verificar trazabilidad
        $this->info("\n🔄 Registros de trazabilidad:");
        $traceability = Traceability::whereIn('user_id', [1, 2, 3, 4])->get();
        foreach ($traceability as $trace) {
            $this->line("   Usuario {$trace->user_id}: Variables={$trace->variables}, Matriz={$trace->matriz}, Hipótesis={$trace->hypothesis}");
        }
        
        // Verificar notas
        $this->info("\n📝 Notas creadas:");
        $notes = Note::whereIn('user_id', [1, 2, 3, 4])->get();
        foreach ($notes as $note) {
            $this->line("   - {$note->title} (Usuario: {$note->user_id})");
        }
        
        // Verificar matriz
        $this->info("\n📋 Registros de matriz:");
        $matriz = Matriz::whereIn('user_id', [1, 2])->get();
        $this->line("   Total: {$matriz->count()} registros");
        
        // Verificar hipótesis
        $this->info("\n🔬 Hipótesis creadas:");
        $hypotheses = Hypothesis::whereIn('user_id', [1, 2])->get();
        $this->line("   Total: {$hypotheses->count()} hipótesis");
        
        // Verificar escenarios
        $this->info("\n🎯 Escenarios creados:");
        $scenarios = Scenarios::whereIn('user_id', [1, 2])->get();
        foreach ($scenarios as $scenario) {
            $this->line("   - {$scenario->titulo} (Usuario: {$scenario->user_id})");
        }
        
        // Verificar conclusiones
        $this->info("\n📋 Conclusiones creadas:");
        $conclusions = Conclusion::whereIn('user_id', [1, 2])->get();
        $this->line("   Total: {$conclusions->count()} conclusiones");
        
        $this->info("\n✅ Verificación completada!");
        
        return 0;
    }
} 