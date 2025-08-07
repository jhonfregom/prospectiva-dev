<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('variables_map_analiyis', function (Blueprint $table) {
            $table->integer('edits')->default(0)->after('state');
        });
    }

    public function down(): void
    {
        Schema::table('variables_map_analiyis', function (Blueprint $table) {
            $table->dropColumn('edits');
        });
    }
};