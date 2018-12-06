<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProxyColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sozluk_crawlers', function (Blueprint $table) {
            $table->boolean('proxy')->default(false);
        });

        Schema::table('shopping_crawlers', function (Blueprint $table) {
            $table->boolean('proxy')->default(false);
        });

        Schema::table('media_crawlers', function (Blueprint $table) {
            $table->boolean('proxy')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
