<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwitterAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twitter_accounts', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned()->unique();

            $table->string('token');
            $table->string('token_secret');
            $table->string('name');
            $table->string('screen_name');
            $table->string('avatar');
            $table->string('description');

            $table->boolean('suspended')->default(0);
            $table->boolean('status')->default(0);

            $table->string('reasons')->nullable()->default(null);

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
        Schema::dropIfExists('twitter_accounts');
    }
}
