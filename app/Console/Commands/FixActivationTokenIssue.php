<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class FixActivationTokenIssue extends Command
{
    protected $signature = 'fix:activation-token-issue';
    protected $description = 'Soluciona el problema de campos de activación faltantes para usuarios que descargan desde Git';

    public function handle()
    {
        $this->info("🔧 Solucionando problema de activation_token...");
        $this->line('');

        // Verificar si los campos ya existen
        $hasActivationToken = Schema::hasColumn('users', 'activation_token');
        $hasActivationExpires = Schema::hasColumn('users', 'activation_token_expires_at');

        if ($hasActivationToken && $hasActivationExpires) {
            $this->info("✅ Los campos de activación ya existen en la base de datos");
            $this->info("   - activation_token: ✅");
            $this->info("   - activation_token_expires_at: ✅");
            return 0;
        }

        $this->warn("⚠️ Campos de activación faltantes detectados:");
        if (!$hasActivationToken) {
            $this->error("   - activation_token: ❌ FALTANTE");
        }
        if (!$hasActivationExpires) {
            $this->error("   - activation_token_expires_at: ❌ FALTANTE");
        }

        $this->line('');
        $this->info("🛠️ Creando migraciones faltantes...");

        // Crear migración para los campos de activación
        try {
            Artisan::call('make:migration', [
                'name' => 'add_activation_fields_to_users_table',
                '--table' => 'users'
            ]);
            $this->info("✅ Migración creada: add_activation_fields_to_users_table");
        } catch (\Exception $e) {
            $this->error("❌ Error creando migración: " . $e->getMessage());
            return 1;
        }

        // Obtener el archivo de migración recién creado
        $migrationFiles = glob(database_path('migrations/*add_activation_fields_to_users_table.php'));
        if (empty($migrationFiles)) {
            $this->error("❌ No se pudo encontrar el archivo de migración creado");
            return 1;
        }

        $migrationFile = $migrationFiles[0];
        $this->info("📝 Editando migración: " . basename($migrationFile));

        // Leer el contenido actual
        $content = file_get_contents($migrationFile);
        
        // Reemplazar el método up()
        $newUpMethod = '    public function up(): void
    {
        Schema::table(\'users\', function (Blueprint $table) {
            $table->string(\'activation_token\', 255)->nullable()->after(\'password\');
            $table->timestamp(\'activation_token_expires_at\')->nullable()->after(\'activation_token\');
        });
    }';

        $content = preg_replace(
            '/public function up\(\): void\s*\{[^}]*\}/s',
            $newUpMethod,
            $content
        );

        // Reemplazar el método down()
        $newDownMethod = '    public function down(): void
    {
        Schema::table(\'users\', function (Blueprint $table) {
            $table->dropColumn([\'activation_token\', \'activation_token_expires_at\']);
        });
    }';

        $content = preg_replace(
            '/public function down\(\): void\s*\{[^}]*\}/s',
            $newDownMethod,
            $content
        );

        // Guardar el archivo editado
        file_put_contents($migrationFile, $content);
        $this->info("✅ Migración editada correctamente");

        // Ejecutar la migración
        $this->line('');
        $this->info("🚀 Ejecutando migración...");
        try {
            Artisan::call('migrate');
            $this->info("✅ Migración ejecutada correctamente");
        } catch (\Exception $e) {
            $this->error("❌ Error ejecutando migración: " . $e->getMessage());
            return 1;
        }

        // Verificar que los campos se crearon correctamente
        $this->line('');
        $this->info("🔍 Verificando campos creados...");
        
        $hasActivationTokenAfter = Schema::hasColumn('users', 'activation_token');
        $hasActivationExpiresAfter = Schema::hasColumn('users', 'activation_token_expires_at');

        if ($hasActivationTokenAfter && $hasActivationExpiresAfter) {
            $this->info("🎉 ¡Problema solucionado exitosamente!");
            $this->info("✅ activation_token: CREADO");
            $this->info("✅ activation_token_expires_at: CREADO");
            $this->line('');
            $this->info("💡 El sistema de activación de usuarios ahora debería funcionar correctamente.");
        } else {
            $this->error("❌ Los campos no se crearon correctamente");
            return 1;
        }

        return 0;
    }
}
