<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueToTwitterTokens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('twitter_tokens', function (Blueprint $table) {
            $table->unique([ 'consumer_key', 'access_token' ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('twitter_tokens', function (Blueprint $table) {
            $table->dropUnique([ 'consumer_key', 'access_token' ]);
        });
    }
}
