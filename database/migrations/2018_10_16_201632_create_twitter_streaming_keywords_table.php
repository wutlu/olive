<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwitterStreamingKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twitter_streaming_keywords', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('keyword')->index();
            $table->string('reasons')->nullable()->default(null);

            $table->boolean('status')->default(0);

            $table->unsignedInteger('organisation_id')->nullable()->default(null);
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade')->onUpdate('cascade');

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
        Schema::dropIfExists('twitter_streaming_keywords');
    }
}
