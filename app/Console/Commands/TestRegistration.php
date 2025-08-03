<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestRegistration extends Command
{
    protected $signature = 'test:registration';
    protected $description = 'Probar el registro de usuarios';

    public function handle()
    {
        $this->info('🧪 Probando registro de usuarios...');

        // Limpiar usuarios de prueba anteriores
        User::where('user', 'like', 'test_%')->delete();

        // Probar registro de usuario natural
        $this->info("\n👤 Probando registro de usuario natural...");
        try {
            $naturalUser = User::create([
                'document_id' => 12345678,
                'first_name' => 'Juan',
                'last_name' => 'Pérez',
                'user' => 'test_natural@example.com',
                'password' => Hash::make('password123'),
                'registration_type' => 'natural',
                'economic_sector' => 1,
                'status_users_id' => 2,
            ]);
            $this->info("   ✅ Usuario natural creado: {$naturalUser->user}");
        } catch (\Exception $e) {
            $this->error("   ❌ Error creando usuario natural: " . $e->getMessage());
            return 1;
        }

        // Probar registro de usuario empresa
        $this->info("\n🏢 Probando registro de usuario empresa...");
        try {
            $companyUser = User::create([
                'document_id' => 987654321,
                'first_name' => 'TechCorp Solutions',
                'last_name' => '',
                'user' => 'test_company@example.com',
                'password' => Hash::make('password123'),
                'registration_type' => 'company',
                'economic_sector' => 2,
                'status_users_id' => 2,
            ]);
            $this->info("   ✅ Usuario empresa creado: {$companyUser->user}");
        } catch (\Exception $e) {
            $this->error("   ❌ Error creando usuario empresa: " . $e->getMessage());
            return 1;
        }

        // Verificar usuarios creados
        $this->info("\n📋 Verificando usuarios creados:");
        $users = User::where('user', 'like', 'test_%')->get();
        foreach ($users as $user) {
            $this->info("   - {$user->user} ({$user->registration_type}) - {$user->first_name}");
        }

        $this->info("\n🎉 ¡Prueba de registro completada exitosamente!");
        return 0;
    }
} 