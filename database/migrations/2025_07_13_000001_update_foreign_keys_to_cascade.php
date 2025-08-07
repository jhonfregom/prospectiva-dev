<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        
        Schema::table('hypothesis', function (Blueprint $table) {
            $table->dropForeign(['id_variable']);
            $table->foreign('id_variable')->references('id')->on('variables')->onDelete('cascade');
        });
        
        Schema::table('matriz', function (Blueprint $table) {
            $table->dropForeign('matriz_id_variable_foreign');
            $table->foreign('id_variable')->references('id')->on('variables')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        
        Schema::table('hypothesis', function (Blueprint $table) {
            $table->dropForeign(['id_variable']);
            $table->foreign('id_variable')->references('id')->on('variables')->onDelete('NO ACTION');
        });
        
        Schema::table('matriz', function (Blueprint $table) {
            $table->dropForeign(['id_variable']);
            $table->foreign('id_variable')->references('id')->on('variables')->onDelete('NO ACTION');
        });
    }
};