<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYoutubeFollowingKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('youtube_following_keywords', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('keyword')->index();
            $table->string('reason')->nullable()->default(null);

            $table->boolean('status')->default(0);

            $table->unsignedInteger('organisation_id')->nullable()->default(null)->index();
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade')->onUpdate('cascade');

            $table->unique([ 'keyword', 'organisation_id' ]);

            $table->unsignedInteger('hit')->default(0);

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
        Schema::dropIfExists('youtube_following_keywords');
    }
}
