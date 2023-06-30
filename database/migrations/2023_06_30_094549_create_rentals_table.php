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
            $table->integer('user_id');
            $table->enum('payment_status',['HALF','FULL'],50)->default('HALF');
            $table->enum('status',['PENDING','APPROVED','DENINED','CANCEL'],50)->default('PENDING');
            $table->string('address',255);
            $table->boolean('is_shipping');
            $table->date('shipping_date');
            $table->boolean('is_picking');
            $table->date('picking_date');
            $table->decimal('total_price',10,2);
            $table->decimal('total_broken_price',10,2);
            $table->string('reciept_half_image',255);
            $table->string('reciept_full_image',255);
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
