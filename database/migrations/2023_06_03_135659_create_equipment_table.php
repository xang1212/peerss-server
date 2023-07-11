<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name',100);
            $table->enum('category',["FAN","FORK", "SPOON", "BOWL", "CHAIR", "TABLE", "TENT", "DISH"],50)->default('CHAIR');
            $table->string('desc');
            $table->integer('qty');
            $table->decimal('price',10,2);
            $table->decimal('broken_price',8,2);
            $table->string('unit',20);
            $table->string("images",600)->nullable();
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
        Schema::dropIfExists('equipment');
    }
}
