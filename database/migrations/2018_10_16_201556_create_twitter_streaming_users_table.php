<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwitterStreamingUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twitter_streaming_users', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('screen_name')->nullable()->default(null);
            $table->unsignedBigInteger('user_id');
            $table->string('reasons')->nullable()->default(null);

            $table->boolean('status')->default(0);

            $table->unsignedInteger('organisation_id')->nullable()->default(null);
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade')->onUpdate('cascade');

            $table->unique([ 'user_id', 'organisation_id' ]);

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
        Schema::dropIfExists('twitter_streaming_users');
    }
}
