<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Expression;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('conclusions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id')->autoIncrement();
            $table->text('component_practice')->nullable();            
            $table->integer('component_practice_edits')->default(0);
            $table->text('actuality')->nullable();            
            $table->integer('actuality_edits')->default(0);
            $table->text('aplication')->nullable();
            $table->integer('aplication_edits')->default(0);
            $table->integer('user_id');
            $table->enum('state', ['0', '1'])->default('0');
            $table->integer('tried_id')->nullable();
            $table->datetime('created_at')->default(new Expression('CURRENT_TIMESTAMP'));
            $table->datetime('updated_at')->default(new Expression('CURRENT_TIMESTAMP'));
            $table->primary([ 'id','user_id' ]);

            $table->index([
                    'user_id',
                ],
                'user_id_indexes'
            );

                $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');

                $table->foreign('tried_id')->references('id')->on('traceability')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');
        });

        DB::statement("ALTER TABLE conclusions MODIFY id INT AUTO_INCREMENT");
    }

    public function down(): void
    {
        Schema::dropIfExists('conclusions');
    }
};