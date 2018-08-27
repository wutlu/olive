<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaCrawlersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_crawlers', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('name')->unique();
            $table->string('link');
            $table->string('base')->default('/');
            $table->string('pattern_url');
            $table->string('selector_title');
            $table->string('selector_description');

            $table->smallInteger('error_count')->default(0);
            $table->smallInteger('off_limit')->default(10);
            $table->text('off_reason')->nullable()->default(null);

            $table->smallInteger('control_interval')->default(10);
            $table->timestamp('control_date')->default(\DB::raw('now()'));

            $table->boolean('status')->default(0);
            $table->boolean('test')->default(0);
            $table->string('elasticsearch_index_name')->nullable()->default(null);

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
        Schema::dropIfExists('media_crawlers');
    }
}
