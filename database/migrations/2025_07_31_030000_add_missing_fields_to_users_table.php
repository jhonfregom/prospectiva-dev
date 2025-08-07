<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            
            if (!Schema::hasColumn('users', 'economic_sector')) {
                $table->integer('economic_sector')->nullable();
            }
            if (!Schema::hasColumn('users', 'registration_type')) {
                $table->string('registration_type', 50)->default('natural');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            
            $table->dropColumn(['economic_sector', 'registration_type']);
        });
    }
};