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
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('payment_status',['HALF','FULL'],50)->default('HALF');
            $table->enum('status',['PENDING','APPROVED','DENIED','CANCEL'],50)->default('PENDING');
            $table->string('address',255)->nullable();
            $table->enum('is_shipping',['YES','NO'],50)->default('NO');
            $table->date('shipping_date')->nullable();
            $table->enum('is_picking',['YES','NO'],50)->default('NO');
            $table->date('picking_date')->nullable();
            $table->decimal('total_price',10,2)->default(0);
            $table->decimal('total_broken_price',10,2)->nullable();
            $table->enum('type',['INDIVIDUAL', 'PACKAGE'],50)->default('INDIVIDUAL');
            $table->string('receipt_half_image',255)->nullable();
            $table->string('receipt_full_image',255)->nullable();
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
