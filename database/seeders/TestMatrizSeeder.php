<?php

namespace Database\Seeders;

use App\Models\Matriz;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestMatrizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('DELETE FROM matriz WHERE id > 0');
        DB::statement('ALTER TABLE matriz AUTO_INCREMENT = 0');

    $matriz = ([
        [
            'id_matriz' => 1,
            'id_variable' => 1,
            'id_resp_depen' => 3,
            'id_resp_influ' => 1,
            'user_id' => 1,
            'state' => '0'
        ],
        [
            'id_matriz' => 1,
            'id_variable' => 2,
            'id_resp_depen' => 3,
            'id_resp_influ' => 1,
            'user_id' => 1,
            'state' => '0'
        ],
        [
            'id_matriz' => 1,
            'id_variable' => 3,
            'id_resp_depen' => 3,
            'id_resp_influ' => 3,
            'user_id' => 1,
            'state' => '0'
        ],
        [
            'id_matriz' => 1,
            'id_variable' => 4,
            'id_resp_depen' => 1,
            'id_resp_influ' => 1,
            'user_id' => 1,
            'state' => '0'
        ],
        [
            'id_matriz' => 1,
            'id_variable' => 1,
            'id_resp_depen' => 4,
            'id_resp_influ' => 2,
            'user_id' => 1,
            'state' => '0'
        ],
        [
            'id_matriz' => 1,
            'id_variable' => 2,
            'id_resp_depen' => 4,
            'id_resp_influ' => 1,
            'user_id' => 1,
            'state' => '0'
        ],
        [
            'id_matriz' => 1,
            'id_variable' => 3,
            'id_resp_depen' => 4,
            'id_resp_influ' => 2,
            'user_id' => 1,
            'state' => '0'
        ]
    ]);
    foreach ($matriz as $data) {
    Matriz::create($data);
}
    }
}
