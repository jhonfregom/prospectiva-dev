<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('variables', function (Blueprint $table) {
            $table->integer('edits_variable')->default(0)->after('score');
            $table->integer('edits_now_condition')->default(0)->after('edits_variable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('variables', function (Blueprint $table) {
            $table->dropColumn(['edits_variable', 'edits_now_condition']);
        });
    }
}; 
 