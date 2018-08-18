<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscountDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount_days', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->date('first_day');
            $table->date('last_day');

            $table->unsignedSmallInteger('discount_rate')->default(0);

            $table->decimal('discount_price', 9, 2)->default(0);

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
        Schema::dropIfExists('discount_days');
    }
}
