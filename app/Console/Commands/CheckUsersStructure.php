<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckUsersStructure extends Command
{
    protected $signature = 'users:check-structure';
    protected $description = 'Verificar la estructura actual de la tabla users';

    public function handle()
    {
        $this->info('🔍 Verificando estructura actual de la tabla users...');
        
        $columns = DB::select('DESCRIBE users');
        
        $this->table(
            ['Campo', 'Tipo', 'Null', 'Key', 'Default', 'Extra'],
            array_map(function($column) {
                return [
                    $column->Field,
                    $column->Type,
                    $column->Null,
                    $column->Key,
                    $column->Default,
                    $column->Extra
                ];
            }, $columns)
        );
        
        return 0;
    }
}
