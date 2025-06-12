<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('variables', function (Blueprint $table) {
            // Primero eliminamos la restricción ENUM
            DB::statement("ALTER TABLE variables MODIFY state VARCHAR(1) NOT NULL DEFAULT '0'");
            
            // Luego modificamos la columna para que sea un TINYINT
            DB::statement("ALTER TABLE variables MODIFY state TINYINT NOT NULL DEFAULT 0");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('variables', function (Blueprint $table) {
            // Revertimos a ENUM
            DB::statement("ALTER TABLE variables MODIFY state ENUM('0', '1') NOT NULL DEFAULT '0'");
        });
    }
}; 