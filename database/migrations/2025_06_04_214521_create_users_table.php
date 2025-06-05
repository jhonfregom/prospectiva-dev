<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id');
            $table->integer('document_id');
            $table->string('first_name',200);
            $table->string('last_name',200);
            $table->string('user',100);
            $table->text('password');
            $table->integer('status_users_id');
            $table->dateTime('created_at')->default(new Expression('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(new Expression('CURRENT_TIMESTAMP'));
            $table->rememberToken();
            $table->primary([ 'id', 'document_id', 'status_users_id' ]);

           /* $table->foreign('roles_id')->references('id')->on('roles')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');*/

            $table->foreign('status_users_id')->references('id')->on('status_users')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');

           // $table->index('roles_id');
            $table->unique('user');
        });

        //Add autoincrement to id
        DB::statement("ALTER TABLE users MODIFY id INT AUTO_INCREMENT");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
