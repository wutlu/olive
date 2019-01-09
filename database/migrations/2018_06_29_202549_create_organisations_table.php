<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organisations', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('name');

            $table->unsignedSmallInteger('capacity')->default(1);

            $table->datetime('start_date');
            $table->datetime('end_date');

            $table->unsignedInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->boolean('status')->default(0);

            $table->unsignedInteger('twitter_follow_limit_user')->default(10);
            $table->unsignedInteger('twitter_follow_limit_keyword')->default(10);

            $table->unsignedInteger('youtube_follow_limit_channel')->default(10);
            $table->unsignedInteger('youtube_follow_limit_video')->default(10);
            $table->unsignedInteger('youtube_follow_limit_keyword')->default(10);

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
        Schema::dropIfExists('organisations');
    }
}
