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
        Schema::table('hypothesis', function (Blueprint $table) {
            $table->text('secondary_hypotheses')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hypothesis', function (Blueprint $table) {
            $table->dropColumn('secondary_hypotheses');
        });
    }
};
