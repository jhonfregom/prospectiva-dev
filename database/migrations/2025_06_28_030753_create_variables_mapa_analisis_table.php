<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Database\Query\Expression;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('variables_map_analiyis', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id')->autoIncrement();
            $table->text('description')->nullable();            
            $table->integer('score');
            $table->integer('zone_id');
            $table->integer('user_id');
            $table->enum('state', ['0', '1'])->default('0');
            $table->datetime('created_at')->default(new Expression('CURRENT_TIMESTAMP'));
            $table->datetime('updated_at')->default(new Expression('CURRENT_TIMESTAMP'));
            $table->primary([ 'id', 'zone_id','user_id' ]);

            $table->index([
                    'user_id',
                ],
                'user_id_indexes'
            );

            $table->foreign('zone_id')->references('id')->on('zones')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');

                $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');
        });

        DB::statement("ALTER TABLE variables_map_analiyis MODIFY id INT AUTO_INCREMENT");
    }

    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variables_map_analiyis');
    }
};
