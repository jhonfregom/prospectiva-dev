<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('traceability', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id')->autoIncrement();            
            $table->enum('tried', ['1','2'])->default('1');
            $table->enum('variables', ['0', '1'])->default('0');
            $table->enum('matriz', ['0', '1'])->default('0');
            $table->enum('maps', ['0', '1'])->default('0');
            $table->enum('hypothesis', ['0', '1'])->default('0');
            $table->enum('shwartz', ['0', '1'])->default('0');
            $table->enum('conditions', ['0', '1'])->default('0');
            $table->enum('scenarios', ['0', '1'])->default('0');
            $table->enum('conclusions', ['0', '1'])->default('0');
            $table->enum('results', ['0', '1'])->default('0');
            $table->enum('state', ['0', '1'])->default('0');
            $table->integer('user_id');
            $table->datetime('created_at')->default(new Expression('CURRENT_TIMESTAMP'));
            $table->datetime('updated_at')->default(new Expression('CURRENT_TIMESTAMP'));
            $table->primary([ 'id' ]);
            $table->index([
                    'user_id',
                ],
                'user_id_indexes'
            );
                $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');

        });

        DB::statement("ALTER TABLE traceability MODIFY id INT AUTO_INCREMENT");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traceability');
    }
};
