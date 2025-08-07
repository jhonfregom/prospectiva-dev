<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Note;
use App\Models\Traceability;
use Illuminate\Support\Facades\DB;

class CheckSeederData extends Command
{
    protected $signature = 'check:seeder-data';
    protected $description = 'Verificar los datos creados por el seeder';

    public function handle()
    {
        $this->info('🔍 Verificando datos del seeder...');

        $users = User::all();
        $this->info("📊 Total de usuarios: " . $users->count());

        $this->info("\n📝 Notas por usuario:");
        foreach ($users as $user) {
            $noteCount = Note::where('user_id', $user->id)->count();
            $this->line("   Usuario {$user->id} ({$user->first_name} {$user->last_name}): {$noteCount} notas");
        }

        $this->info("\n🔗 Trazabilidad por usuario:");
        foreach ($users as $user) {
            $traceCount = Traceability::where('user_id', $user->id)->count();
            $this->line("   Usuario {$user->id} ({$user->first_name} {$user->last_name}): {$traceCount} registros");
        }

        $this->info("\n📈 Distribución de notas:");
        $noteDistribution = Note::select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->orderBy('user_id')
            ->get();
            
        foreach ($noteDistribution as $dist) {
            $user = User::find($dist->user_id);
            $userName = $user ? $user->first_name . ' ' . $user->last_name : 'Usuario ' . $dist->user_id;
            $this->line("   {$userName}: {$dist->total} notas");
        }
        
        $this->info("\n✅ Verificación completada");
    }
}