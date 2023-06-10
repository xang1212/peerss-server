<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->id();
            $table->string('first_name',100);
            $table->string('last_name',100);
            $table->enum('gender',['male','female'],50)->default('male');
            $table->enum('role',['owner','staff','customer'],50)->default('customer');
            $table->string('responsibility',255);
            $table->enum('status',['active','inactive'],50)->default('active');
            $table->string('address',255);
            $table->string('phone_number',20);
            $table->string('profile_image',255)->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
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
