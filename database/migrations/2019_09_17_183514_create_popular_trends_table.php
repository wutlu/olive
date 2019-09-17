<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePopularTrendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('popular_trends', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();

            $table->string('module')->index();
            $table->string('social_id')->index();

            $table->unsignedInteger('trend_hit')->default(0);
            $table->unsignedInteger('exp_trend_hit')->default(0);

            $table->unsignedInteger('private_hit')->default(0);

            $table->json('details');

            $table->unique([ 'module', 'social_id' ]);

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
        Schema::dropIfExists('popular_trends');
    }
}
