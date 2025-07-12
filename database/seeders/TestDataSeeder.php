<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StateUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar datos existentes y reiniciar auto-incremento
        DB::statement('DELETE FROM users WHERE id > 0');
        DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');

        // Usuario de prueba original (admin)
        if (!\App\Models\User::where('user', 'test@example.com')->exists()) {
            User::create([
                'id' => 1,
                'first_name' => 'Test User',
                'last_name' => 'Example',
                'document_id' => 123456789,
                'user' => 'test@example.com',
                'password' => Hash::make('abcd1234'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
                'role' => 1 // Administrador
            ]);
        }

        // 10 usuarios adicionales con datos aleatorios
        $users = [
            [
                'id' => 2,
                'first_name' => 'María',
                'last_name' => 'González',
                'document_id' => 100123456,
                'user' => 'maria.gonzalez@empresa.com',
                'password' => Hash::make('password123'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
                'role' => 0
            ],
            [
                'id' => 3,
                'first_name' => 'Carlos',
                'last_name' => 'Rodríguez',
                'document_id' => 200234567,
                'user' => 'carlos.rodriguez@empresa.com',
                'password' => Hash::make('password123'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
                'role' => 0
            ],
            [
                'id' => 4,
                'first_name' => 'Ana',
                'last_name' => 'López',
                'document_id' => 300345678,
                'user' => 'ana.lopez@empresa.com',
                'password' => Hash::make('password123'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
                'role' => 0
            ],
            [
                'id' => 5,
                'first_name' => 'Luis',
                'last_name' => 'Martínez',
                'document_id' => 400456789,
                'user' => 'luis.martinez@empresa.com',
                'password' => Hash::make('password123'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
                'role' => 0
            ],
            [
                'id' => 6,
                'first_name' => 'Sofia',
                'last_name' => 'Pérez',
                'document_id' => 500567890,
                'user' => 'sofia.perez@empresa.com',
                'password' => Hash::make('password123'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
                'role' => 0
            ],
            [
                'id' => 7,
                'first_name' => 'Diego',
                'last_name' => 'Hernández',
                'document_id' => 600678901,
                'user' => 'diego.hernandez@empresa.com',
                'password' => Hash::make('password123'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
                'role' => 0
            ],
            [
                'id' => 8,
                'first_name' => 'Valentina',
                'last_name' => 'García',
                'document_id' => 700789012,
                'user' => 'valentina.garcia@empresa.com',
                'password' => Hash::make('password123'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
                'role' => 0
            ],
            [
                'id' => 9,
                'first_name' => 'Andrés',
                'last_name' => 'Fernández',
                'document_id' => 800890123,
                'user' => 'andres.fernandez@empresa.com',
                'password' => Hash::make('password123'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
                'role' => 0
            ],
            [
                'id' => 10,
                'first_name' => 'Camila',
                'last_name' => 'Torres',
                'document_id' => 900901234,
                'user' => 'camila.torres@empresa.com',
                'password' => Hash::make('password123'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
                'role' => 0
            ],
            [
                'id' => 11,
                'first_name' => 'Juan',
                'last_name' => 'Ramírez',
                'document_id' => 101012345,
                'user' => 'juan.ramirez@empresa.com',
                'password' => Hash::make('password123'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
                'role' => 0
            ]
        ];

        foreach ($users as $userData) {
            if (!User::where('user', $userData['user'])->exists()) {
                User::create($userData);
            }
        }
    }
}
