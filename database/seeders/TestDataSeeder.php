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
         DB::statement('DELETE FROM users WHERE id > 0');
         DB::statement('ALTER TABLE users AUTO_INCREMENT = 0');

    $user = User::create([
            'first_name' => 'Test User',
            'last_name' => 'Example',
            'document_id' => 123456789,
            'user' => 'test@example.com',
            'password' => Hash::make('abcd1234'),
            'status_users_id' => StateUser::STATUS_INACTIVE
        ]);
    }
}
