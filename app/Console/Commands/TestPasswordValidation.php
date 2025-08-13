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
            'valid2' => 'MySecure@123',
            'valid_hash' => 'Password123#',
            'valid_plus' => 'Password123+',
            'valid_paren' => 'Password123(',
            'valid_bracket' => 'Password123[',
            'valid_quote' => 'Password123"',
            // Pruebas específicas para caracteres especiales
            'test_at' => 'Password123@',
            'test_dollar' => 'Password123$',
            'test_exclamation' => 'Password123!',
            'test_percent' => 'Password123%',
            'test_asterisk' => 'Password123*',
            'test_question' => 'Password123?',
            'test_ampersand' => 'Password123&',
            'test_hash' => 'Password123#',
            'test_caret' => 'Password123^',
            'test_plus' => 'Password123+',
            'test_equals' => 'Password123=',
            'test_paren_open' => 'Password123(',
            'test_paren_close' => 'Password123)',
            'test_bracket_open' => 'Password123[',
            'test_bracket_close' => 'Password123]',
            'test_brace_open' => 'Password123{',
            'test_brace_close' => 'Password123}',
            'test_pipe' => 'Password123|',
            'test_backslash' => 'Password123\\',
            'test_colon' => 'Password123:',
            'test_semicolon' => 'Password123;',
            'test_quote' => 'Password123"',
            'test_single_quote' => 'Password123\'',
            'test_less_than' => 'Password123<',
            'test_greater_than' => 'Password123>',
            'test_comma' => 'Password123,',
            'test_period' => 'Password123.',
            'test_slash' => 'Password123/',
            'test_tilde' => 'Password123~',
            'test_backtick' => 'Password123`'
        ];
        
        $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?~`])[A-Za-z\d!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?~`]/';
        
        foreach ($passwords as $type => $password) {
            $this->info("Probando: {$type} = '{$password}'");
            
            $length = strlen($password) >= 8;
            $hasLowercase = preg_match('/[a-z]/', $password);
            $hasUppercase = preg_match('/[A-Z]/', $password);
            $hasNumber = preg_match('/\d/', $password);
            $hasSpecial = preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?~`]/', $password);
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
        
        $this->info("=== PRUEBA DE VALIDACIÓN JAVASCRIPT ===");
        $this->line('');
        
        // Simular la validación JavaScript del frontend
        $jsRegex = '/[@$!%*?&#^+=()\[\]{}|\\\\:;"\'<>,.\/~`]/';
        
        foreach ($passwords as $type => $password) {
            $jsSpecial = preg_match($jsRegex, $password);
            $this->line("JS - {$type}: " . ($jsSpecial ? '✅' : '❌') . " (carácter especial detectado)");
        }
        
        $this->info("=== RESUMEN ===");
        $this->info("Las contraseñas válidas deben cumplir TODOS los requisitos:");
        $this->info("✅ Mínimo 8 caracteres");
        $this->info("✅ Al menos una letra minúscula");
        $this->info("✅ Al menos una letra mayúscula");
        $this->info("✅ Al menos un número");
        $this->info("✅ Al menos un carácter especial (!@#$%^&*()_+-=[]{};':\"\|,.<>/?~`)");
    }
}
