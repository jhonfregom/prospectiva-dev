<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $columns = DB::select("DESCRIBE variables_map_analiyis");
    $editsExists = false;
    
    foreach ($columns as $column) {
        if ($column->Field === 'edits') {
            $editsExists = true;
            break;
        }
    }
    
    if ($editsExists) {
        echo "El campo 'edits' existe en la tabla variables_map_analiyis\n";
    } else {
        echo "El campo 'edits' NO existe en la tabla variables_map_analiyis\n";
        echo "Columnas existentes:\n";
        foreach ($columns as $column) {
            echo "- " . $column->Field . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 