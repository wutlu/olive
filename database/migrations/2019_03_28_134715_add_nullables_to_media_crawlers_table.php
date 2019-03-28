<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNullablesToMediaCrawlersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('media_crawlers', function (Blueprint $table) {
            $table->string('url_pattern')->nullable()->change();
            $table->string('selector_title')->nullable()->change();
            $table->string('selector_description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('media_crawlers', function (Blueprint $table) {
            $table->string('url_pattern')->nullable(false)->change();
            $table->string('selector_title')->nullable(false)->change();
            $table->string('selector_description')->nullable(false)->change();
        });
    }
}
