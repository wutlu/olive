<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYoutubeFollowingChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('youtube_following_channels', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('channel_image');
            $table->string('channel_name');
            $table->string('channel_id')->index();

            $table->string('reason')->nullable()->default(null);

            $table->boolean('status')->default(0);

            $table->unsignedInteger('organisation_id')->nullable()->default(null)->index();
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade')->onUpdate('cascade');

            $table->unique([ 'channel_id', 'organisation_id' ]);

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
        Schema::dropIfExists('youtube_following_channels');
    }
}
