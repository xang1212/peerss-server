<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRentalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->enum('payment_status',['HALF','FULL'],50)->default('HALF');
            $table->enum('status',['PENDING','APPROVED','DENINED','CANCEL'],50)->default('PENDING');
            $table->string('address',255)->nullable();
            $table->boolean('is_shipping')->nullable();
            $table->date('shipping_date')->nullable();
            $table->boolean('is_picking')->nullable();
            $table->date('picking_date')->nullable();
            $table->decimal('total_price',10,2)->default(0);
            $table->decimal('total_broken_price',10,2)->nullable();
            $table->string('reciept_half_image',255)->nullable();
            $table->string('reciept_full_image',255)->nullable();
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
        Schema::dropIfExists('rentals');
    }
}