<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('matriz', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id');
            $table->integer('id_matriz');            
            $table->integer('id_variable');
            $table->integer('id_resp_depen');
            $table->integer('id_resp_influ');
            $table->integer('user_id');
            $table->enum('state', ['0', '1'])->default('0');
            $table->dateTime('created_at')->default(new Expression('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(new Expression('CURRENT_TIMESTAMP'));
            $table->primary([ 'id','user_id' ]);

          $table->index([
                    'user_id',
                ],
                'user_id_indexes'
            );
            $table->index([
                    'id_variable',
                ],
                'id_variable_indexes'
            );

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');
            $table->foreign('id_variable')->references('id')->on('variables')
                ->onUpdate('NO ACTION')
                ->onDelete('cascade');
        });

        DB::statement("ALTER TABLE matriz MODIFY id INT AUTO_INCREMENT");
    }

    public function down(): void
    {
        Schema::dropIfExists('matriz');
    }
};