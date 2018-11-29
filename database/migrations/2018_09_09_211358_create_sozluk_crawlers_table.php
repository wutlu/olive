<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSozlukCrawlersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sozluk_crawlers', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('name')->unique();
            $table->string('site');
            $table->string('url_pattern')->default('/');

            $table->unsignedBigInteger('last_id')->default(1);

            $table->string('selector_title');
            $table->string('selector_entry');
            $table->string('selector_author');

            $table->unsignedSmallInteger('error_count')->default(0);
            $table->unsignedSmallInteger('off_limit')->default(100);
            $table->text('off_reason')->nullable()->default(null);

            $table->unsignedSmallInteger('max_attempt')->default(100);

            $table->boolean('status')->default(0);
            $table->boolean('test')->default(0);
            $table->boolean('elasticsearch_index')->default(0);

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
        Schema::dropIfExists('sozluk_crawlers');
    }
}
