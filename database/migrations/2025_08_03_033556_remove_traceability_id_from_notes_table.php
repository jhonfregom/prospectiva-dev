<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        
        try {
            DB::statement('ALTER TABLE notes DROP FOREIGN KEY notes_traceability_id_foreign');
        } catch (\Exception $e) {
            
        }

        try {
            DB::statement('ALTER TABLE notes DROP INDEX notes_user_id_traceability_id_index');
        } catch (\Exception $e) {
            
        }

        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn('traceability_id');
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            
            $table->integer('traceability_id')->nullable()->after('user_id');

            $table->index(['user_id', 'traceability_id']);

            $table->foreign('traceability_id')->references('id')->on('traceability')->onDelete('cascade');
        });
    }
};