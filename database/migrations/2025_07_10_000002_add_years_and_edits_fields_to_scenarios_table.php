<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('scenarios', function (Blueprint $table) {
            $table->text('year1')->nullable();
            $table->text('year2')->nullable();
            $table->text('year3')->nullable();
            $table->integer('edits_year1')->default(0);
            $table->integer('edits_year2')->default(0);
            $table->integer('edits_year3')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('scenarios', function (Blueprint $table) {
            $table->dropColumn(['year1', 'year2', 'year3', 'edits_year1', 'edits_year2', 'edits_year3']);
        });
    }
};