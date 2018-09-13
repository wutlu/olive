<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShoppingCrawlersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopping_crawlers', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('name')->unique();
            $table->string('site');
            $table->string('google_search_url')->default('https://www.google.com/search?q=site:&tbs=sbd:1,qdr:h');
            $table->string('url_pattern')->default('/');

            $table->string('selector_title');
            $table->string('selector_description');
            $table->string('selector_categories');
            $table->string('selector_address');
            $table->string('selector_ul');
            $table->string('selector_ul_li');
            $table->string('selector_ul_li_key');
            $table->string('selector_ul_li_val');
            $table->string('selector_seller_name');
            $table->string('selector_selles_phones');

            $table->smallInteger('error_count')->default(0);
            $table->smallInteger('off_limit')->default(10);
            $table->text('off_reason')->nullable()->default(null);

            $table->smallInteger('control_interval')->default(10);
            $table->timestamp('control_date')->default(\DB::raw('now()'));

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
        Schema::dropIfExists('shopping_crawlers');
    }
}
