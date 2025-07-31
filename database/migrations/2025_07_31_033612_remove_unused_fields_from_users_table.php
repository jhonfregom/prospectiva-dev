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
            // Eliminar campos no utilizados solo si existen
            $columnsToDrop = ['names', 'surnames', 'company_name', 'nit', 'city_region', 'data_authorization', 'role'];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Restaurar campos eliminados
            $table->string('names', 200)->nullable();
            $table->string('surnames', 200)->nullable();
            $table->string('company_name', 200)->nullable();
            $table->string('nit', 20)->nullable();
            $table->string('city_region', 200)->nullable();
            $table->text('data_authorization')->nullable();
            $table->tinyInteger('role')->default(0);
        });
    }
};
