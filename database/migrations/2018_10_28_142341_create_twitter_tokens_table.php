<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwitterTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twitter_tokens', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->integer('pid')->nullable()->default(null);

            $table->enum('status', [ 'on', 'off', 'disabled', 'restart', 'stop', 'start' ])->default('off');

            $table->string('consumer_key');
            $table->string('consumer_secret');
            $table->string('access_token');
            $table->string('access_token_secret');

            $table->enum('type', [ 'follow', 'track', 'locations' ])->nullable()->default(null);
            $table->string('tmp_key')->nullable()->default(null);
            $table->longText('value')->nullable()->default(null);

            $table->unsignedSmallInteger('error_count')->default(0);
            $table->unsignedSmallInteger('off_limit')->default(10);
            $table->text('off_reason')->nullable()->default(null);

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
        Schema::dropIfExists('twitter_tokens');
    }
}
