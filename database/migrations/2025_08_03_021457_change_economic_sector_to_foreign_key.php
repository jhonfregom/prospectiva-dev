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
        // Primero, convertir los valores de string a integer basándose en el nombre del sector
        $sectors = DB::table('economic_sectors')->get();
        $sectorMap = [];
        
        foreach ($sectors as $sector) {
            $sectorMap[$sector->name] = $sector->id;
        }
        
        // Actualizar los registros existentes
        $users = DB::table('users')->whereNotNull('economic_sector')->get();
        
        foreach ($users as $user) {
            if (isset($sectorMap[$user->economic_sector])) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->where('document_id', $user->document_id)
                    ->where('status_users_id', $user->status_users_id)
                    ->update(['economic_sector' => $sectorMap[$user->economic_sector]]);
            } else {
                // Si no encuentra el sector, establecer como NULL
                DB::table('users')
                    ->where('id', $user->id)
                    ->where('document_id', $user->document_id)
                    ->where('status_users_id', $user->status_users_id)
                    ->update(['economic_sector' => null]);
            }
        }
        
        // Cambiar el tipo de columna de string a unsignedBigInteger para que sea compatible con la clave foránea
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('economic_sector')->nullable()->change();
        });
        
        // Agregar la clave foránea
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('economic_sector')->references('id')->on('economic_sectors')
                ->onUpdate('NO ACTION')
                ->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar la clave foránea
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['economic_sector']);
        });
        
        // Cambiar de vuelta a string
        Schema::table('users', function (Blueprint $table) {
            $table->string('economic_sector', 255)->nullable()->change();
        });
        
        // Convertir de vuelta los IDs a nombres
        $sectors = DB::table('economic_sectors')->get();
        $sectorMap = [];
        
        foreach ($sectors as $sector) {
            $sectorMap[$sector->id] = $sector->name;
        }
        
        $users = DB::table('users')->whereNotNull('economic_sector')->get();
        
        foreach ($users as $user) {
            if (isset($sectorMap[$user->economic_sector])) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->where('document_id', $user->document_id)
                    ->where('status_users_id', $user->status_users_id)
                    ->update(['economic_sector' => $sectorMap[$user->economic_sector]]);
            }
        }
    }
};
