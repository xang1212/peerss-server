<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentBrokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_brokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rental_id');
            $table->foreign('rental_id')->references('id')->on('rentals');
            $table->unsignedBigInteger('equipment_id');
            $table->foreign('equipment_id')->references('id')->on('equipment');
            $table->string('equipment_name',100);
            $table->integer('broken_qty');
            $table->decimal('broken_price',8,2);
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
        Schema::dropIfExists('equipment_brokens');
    }
}
