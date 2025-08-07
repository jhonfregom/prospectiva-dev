<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        
        $sectors = DB::table('economic_sectors')->get();
        $sectorMap = [];
        
        foreach ($sectors as $sector) {
            $sectorMap[$sector->name] = $sector->id;
        }

        $users = DB::table('users')->whereNotNull('economic_sector')->get();
        
        foreach ($users as $user) {
            if (isset($sectorMap[$user->economic_sector])) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->where('document_id', $user->document_id)
                    ->where('status_users_id', $user->status_users_id)
                    ->update(['economic_sector' => $sectorMap[$user->economic_sector]]);
            } else {
                
                DB::table('users')
                    ->where('id', $user->id)
                    ->where('document_id', $user->document_id)
                    ->where('status_users_id', $user->status_users_id)
                    ->update(['economic_sector' => null]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('economic_sector')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('economic_sector')->references('id')->on('economic_sectors')
                ->onUpdate('NO ACTION')
                ->onDelete('SET NULL');
        });
    }

    public function down(): void
    {
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['economic_sector']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('economic_sector', 255)->nullable()->change();
        });

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