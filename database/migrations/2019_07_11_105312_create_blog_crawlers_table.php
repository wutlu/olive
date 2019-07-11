<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlogCrawlersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blog_crawlers', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('name')->unique();
            $table->string('site');
            $table->string('base')->default('/');
            $table->string('url_pattern')->nullable()->default(null);
            $table->string('selector_title')->nullable()->default(null);
            $table->string('selector_description')->nullable()->default(null);

            $table->unsignedSmallInteger('error_count')->default(0);
            $table->unsignedSmallInteger('off_limit')->default(100);
            $table->text('off_reason')->nullable()->default(null);

            $table->unsignedSmallInteger('control_interval')->default(10);
            $table->timestamp('control_date')->default(\DB::raw('now()'));

            $table->boolean('status')->default(0);
            $table->boolean('test')->default(0);
            $table->boolean('standard')->default(0);
            $table->boolean('proxy')->default(false);

            $table->string('elasticsearch_index_name');

            $table->unsignedInteger('count')->default(0);
            $table->unsignedInteger('alexa_rank')->default(0);

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
        Schema::dropIfExists('blog_crawlers');
    }
}
