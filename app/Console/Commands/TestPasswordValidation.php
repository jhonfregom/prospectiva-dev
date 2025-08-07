<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestPasswordValidation extends Command
{
    protected $signature = 'test:password-validation';
    protected $description = 'Probar las validaciones de contraseña';

    public function handle()
    {
        $this->info("=== PRUEBA DE VALIDACIONES DE CONTRASEÑA ===");
        $this->line('');
        
        // Contraseñas de prueba
        $passwords = [
            'weak' => '12345678',
            'no_uppercase' => 'password123!',
            'no_lowercase' => 'PASSWORD123!',
            'no_number' => 'Password!',
            'no_special' => 'Password123',
            'valid' => 'Password123!',
            'valid2' => 'MySecure@123'
        ];
        
        $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/';
        
        foreach ($passwords as $type => $password) {
            $this->info("Probando: {$type} = '{$password}'");
            
            $length = strlen($password) >= 8;
            $hasLowercase = preg_match('/[a-z]/', $password);
            $hasUppercase = preg_match('/[A-Z]/', $password);
            $hasNumber = preg_match('/\d/', $password);
            $hasSpecial = preg_match('/[@$!%*?&]/', $password);
            $matchesRegex = preg_match($regex, $password);
            
            $this->line("  - Longitud ≥ 8: " . ($length ? '✅' : '❌'));
            $this->line("  - Minúscula: " . ($hasLowercase ? '✅' : '❌'));
            $this->line("  - Mayúscula: " . ($hasUppercase ? '✅' : '❌'));
            $this->line("  - Número: " . ($hasNumber ? '✅' : '❌'));
            $this->line("  - Especial: " . ($hasSpecial ? '✅' : '❌'));
            $this->line("  - Regex match: " . ($matchesRegex ? '✅' : '❌'));
            $this->line("  - Resultado: " . ($matchesRegex ? '✅ VÁLIDA' : '❌ INVÁLIDA'));
            $this->line('');
        }
        
        $this->info("=== RESUMEN ===");
        $this->info("Las contraseñas válidas deben cumplir TODOS los requisitos:");
        $this->info("✅ Mínimo 8 caracteres");
        $this->info("✅ Al menos una letra minúscula");
        $this->info("✅ Al menos una letra mayúscula");
        $this->info("✅ Al menos un número");
        $this->info("✅ Al menos un carácter especial (@$!%*?&)");
    }
}
