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
        // Hypothesis: eliminar y recrear la foreign key con cascade
        Schema::table('hypothesis', function (Blueprint $table) {
            $table->dropForeign(['id_variable']);
            $table->foreign('id_variable')->references('id')->on('variables')->onDelete('cascade');
        });
        // Matriz: eliminar y recrear la foreign key con cascade
        Schema::table('matriz', function (Blueprint $table) {
            $table->dropForeign('matriz_id_variable_foreign');
            $table->foreign('id_variable')->references('id')->on('variables')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hypothesis: revertir a NO ACTION
        Schema::table('hypothesis', function (Blueprint $table) {
            $table->dropForeign(['id_variable']);
            $table->foreign('id_variable')->references('id')->on('variables')->onDelete('NO ACTION');
        });
        // Matriz: revertir a NO ACTION
        Schema::table('matriz', function (Blueprint $table) {
            $table->dropForeign(['id_variable']);
            $table->foreign('id_variable')->references('id')->on('variables')->onDelete('NO ACTION');
        });
    }
}; 
 