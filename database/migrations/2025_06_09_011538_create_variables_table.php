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
        Schema::create('variables', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id');
            $table->string('id_variable', 100);
            $table->string('name_variable',length: 80);
            $table->text('description');
            $table->integer('score');
            $table->unsignedBigInteger('user_id');
            $table->enum('state', ['0', '1'])->default('0');
            $table->dateTime('created_at')->default(new Expression('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(new Expression('CURRENT_TIMESTAMP'));
            $table->primary([ 'id', 'user_id' ]);

          $table->index([
                    'user_id',
                ],
                'user_id_indexes'
            );

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');

        });

        //Add autoincrement to id
        DB::statement("ALTER TABLE variables MODIFY id INT AUTO_INCREMENT");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variables');
    }
};
