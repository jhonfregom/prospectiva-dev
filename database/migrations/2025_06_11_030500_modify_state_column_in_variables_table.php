<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('variables', function (Blueprint $table) {
            
            DB::statement("ALTER TABLE variables MODIFY state VARCHAR(1) NOT NULL DEFAULT '0'");

            DB::statement("ALTER TABLE variables MODIFY state TINYINT NOT NULL DEFAULT 0");
        });
    }

    public function down(): void
    {
        Schema::table('variables', function (Blueprint $table) {
            
            DB::statement("ALTER TABLE variables MODIFY state ENUM('0', '1') NOT NULL DEFAULT '0'");
        });
    }
};