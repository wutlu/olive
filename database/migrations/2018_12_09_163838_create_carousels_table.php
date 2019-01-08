<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarouselsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carousels', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('title');
            $table->string('description');
            $table->string('pattern')->nullable()->default(null);

            $table->string('button_action')->nullable()->default(null);
            $table->string('button_text')->nullable()->default(null);

            $table->boolean('carousel')->default(true);
            $table->boolean('modal')->default(false);

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
        Schema::dropIfExists('carousels');
    }
}
