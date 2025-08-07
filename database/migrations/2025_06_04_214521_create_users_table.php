<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
class CreateUsersTable extends Migration
{
    
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
            $table->tinyInteger('role')->default(0); 
            $table->integer('status_users_id');
            $table->dateTime('created_at')->default(new Expression('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(new Expression('CURRENT_TIMESTAMP'));
            $table->rememberToken();
            $table->primary([ 'id', 'document_id', 'status_users_id' ]);

            $table->foreign('status_users_id')->references('id')->on('status_users')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');

            $table->unique('user');
        });

        DB::statement("ALTER TABLE users MODIFY id INT AUTO_INCREMENT");
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}