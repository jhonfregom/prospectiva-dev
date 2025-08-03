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
        Schema::table('users', function (Blueprint $table) {
            // Agregar campos faltantes (email eliminado permanentemente)
            if (!Schema::hasColumn('users', 'economic_sector')) {
                $table->integer('economic_sector')->nullable();
            }
            if (!Schema::hasColumn('users', 'registration_type')) {
                $table->string('registration_type', 50)->default('natural');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remover campos agregados (email no se restaura)
            $table->dropColumn(['economic_sector', 'registration_type']);
        });
    }
};
