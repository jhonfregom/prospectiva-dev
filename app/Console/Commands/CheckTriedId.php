<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Variable;
use App\Models\Matriz;
use App\Models\Hypothesis;
use App\Models\Scenarios;
use App\Models\Conclusion;
use App\Models\Traceability;

class CheckTriedId extends Command
{
    protected $signature = 'check:tried-id';
    protected $description = 'Verificar el tried_id en todas las tablas';

    public function handle()
    {
        $this->info('🔍 Verificando tried_id en todas las tablas...');
        
        $users = User::all();
        
        foreach ($users as $user) {
            $this->info("\n👤 Usuario: {$user->first_name} {$user->last_name} (ID: {$user->id})");

            $traceability = Traceability::where('user_id', $user->id)->first();
            $triedId = $traceability ? $traceability->id : null;
            $this->line("   🔗 Trazabilidad ID: " . ($triedId ?? 'NO TIENE'));
            
            if (!$triedId) {
                $this->error("   ❌ Usuario sin trazabilidad - no podrá ver sus datos");
                continue;
            }

            $variables = Variable::where('user_id', $user->id)->get();
            $this->line("   📊 Variables: " . $variables->count());
            foreach ($variables as $var) {
                $status = $var->tried_id == $triedId ? '✅' : '❌';
                $this->line("      {$status} ID: {$var->id}, tried_id: {$var->tried_id} (debería ser: {$triedId})");
            }

            $hypothesis = Hypothesis::where('user_id', $user->id)->get();
            $this->line("   🔬 Hipótesis: " . $hypothesis->count());
            foreach ($hypothesis as $hyp) {
                $status = $hyp->tried_id == $triedId ? '✅' : '❌';
                $this->line("      {$status} ID: {$hyp->id}, tried_id: {$hyp->tried_id} (debería ser: {$triedId})");
            }

            $scenarios = Scenarios::where('user_id', $user->id)->get();
            $this->line("   🎯 Escenarios: " . $scenarios->count());
            foreach ($scenarios as $scen) {
                $status = $scen->tried_id == $triedId ? '✅' : '❌';
                $this->line("      {$status} ID: {$scen->id}, tried_id: {$scen->tried_id} (debería ser: {$triedId})");
            }

            $conclusions = Conclusion::where('user_id', $user->id)->get();
            $this->line("   📝 Conclusiones: " . $conclusions->count());
            foreach ($conclusions as $concl) {
                $status = $concl->tried_id == $triedId ? '✅' : '❌';
                $this->line("      {$status} ID: {$concl->id}, tried_id: {$concl->tried_id} (debería ser: {$triedId})");
            }
        }
        
        $this->info("\n✅ Verificación completada");
    }
}