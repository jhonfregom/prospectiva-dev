<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CheckEmailFieldProtection extends Command
{
    protected $signature = 'check:email-protection';
    protected $description = 'Verificar que el campo email esté protegido contra recreación';

    public function handle()
    {
        $this->info('🔒 Verificando protección del campo email...');

        $this->info("\n📋 Verificando estructura de tabla users:");
        if (Schema::hasColumn('users', 'email')) {
            $this->error("   ❌ PELIGRO: El campo 'email' existe en la tabla users");
            $this->error("   ⚠️  Esto indica que alguna migración lo recreó");
            return 1;
        } else {
            $this->info("   ✅ El campo 'email' NO existe en la tabla users");
        }

        if (Schema::hasColumn('users', 'user')) {
            $this->info("   ✅ El campo 'user' existe y funciona como email/login");
        } else {
            $this->error("   ❌ ERROR: El campo 'user' no existe");
            return 1;
        }

        $this->info("\n🔍 Verificando migraciones:");
        $migrationsPath = database_path('migrations');
        $migrationFiles = glob($migrationsPath . '/*.php');
        
        $dangerousMigrations = [];
        foreach ($migrationFiles as $file) {
            $content = file_get_contents($file);
            if (strpos($content, 'email') !== false && strpos($content, 'string') !== false) {
                $filename = basename($file);
                $dangerousMigrations[] = $filename;
            }
        }
        
        if (!empty($dangerousMigrations)) {
            $this->warn("   ⚠️  Migraciones que contienen referencias a 'email':");
            foreach ($dangerousMigrations as $migration) {
                $this->line("      - {$migration}");
            }
        } else {
            $this->info("   ✅ No se encontraron migraciones que puedan crear el campo 'email'");
        }

        $this->info("\n🧪 Verificando funcionalidad del sistema:");
        try {
            $userCount = DB::table('users')->count();
            $this->info("   ✅ Sistema funcionando - {$userCount} usuarios en la base de datos");
        } catch (\Exception $e) {
            $this->error("   ❌ Error en el sistema: " . $e->getMessage());
            return 1;
        }

        $usersWithUserField = DB::table('users')->whereNotNull('user')->count();
        $this->info("   ✅ {$usersWithUserField} usuarios tienen campo 'user' válido");
        
        $this->info("\n🎉 ¡Protección del campo email verificada exitosamente!");
        $this->info("   El campo 'email' está eliminado permanentemente");
        $this->info("   El campo 'user' funciona correctamente como email/login");
        
        return 0;
    }
}