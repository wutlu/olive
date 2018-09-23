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
            $table->string('google_search_query')->default('site:sahibinden.com/ilan');
            $table->unsignedSmallInteger('google_max_page')->default(1);
            $table->string('url_pattern')->default('/');

            $table->string('selector_title');
            $table->string('selector_description');
            $table->string('selector_address');
            $table->string('selector_breadcrumb');
            $table->string('selector_seller_name');
            $table->string('selector_seller_phones')->nullable()->default(null);
            $table->string('selector_price');

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
