<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwitterUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twitter_users', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->unsignedBigInteger('user_id')->unique();
            $table->string('token');
            $table->string('token_secret');
            $table->string('nickname')->index();
            $table->string('name')->index();
            $table->string('avatar');
            $table->boolean('verified');

            $table->boolean('status')->default(0);
            $table->string('message')->nullable()->default(null);

            $table->unsignedInteger('organisation_id')->index();
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
        Schema::dropIfExists('twitter_users');
    }
}
