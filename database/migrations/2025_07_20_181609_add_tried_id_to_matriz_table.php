<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('matriz', function (Blueprint $table) {
            $table->integer('tried_id')->nullable()->after('user_id');
            $table->foreign('tried_id')->references('id')->on('traceability')
                ->onUpdate('NO ACTION')
                ->onDelete('CASCADE');
        });
    }

    public function down(): void
    {
        Schema::table('matriz', function (Blueprint $table) {
            $table->dropForeign(['tried_id']);
            $table->dropColumn('tried_id');
        });
    }
};