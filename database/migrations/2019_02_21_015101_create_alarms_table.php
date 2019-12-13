<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlarmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alarms', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('title');
            $table->text('query');

            $table->unsignedSmallInteger('hit')->default(1);

            $table->json('weekdays');

            $table->string('start_time')->default('00:00');
            $table->string('end_time')->default('00:00');

            $table->unsignedInteger('interval')->default(1);

            $table->json('modules');

            $table->unsignedInteger('organisation_id')->index();
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade')->onUpdate('cascade');

            $table->json('user_ids');

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
        Schema::dropIfExists('alarms');
    }
}
