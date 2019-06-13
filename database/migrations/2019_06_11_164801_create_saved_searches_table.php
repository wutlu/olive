<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSavedSearchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('name');

            $table->text('string');

            $table->boolean('illegal')->default(0);
            $table->boolean('reverse')->default(0);

            $table->unsignedSmallInteger('sentiment_pos')->default(0);
            $table->unsignedSmallInteger('sentiment_neu')->default(0);
            $table->unsignedSmallInteger('sentiment_neg')->default(0);
            $table->unsignedSmallInteger('sentiment_hte')->default(0);

            $table->unsignedSmallInteger('consumer_que')->default(0);
            $table->unsignedSmallInteger('consumer_req')->default(0);
            $table->unsignedSmallInteger('consumer_cmp')->default(0);
            $table->unsignedSmallInteger('consumer_nws')->default(0);

            $table->enum('gender', [ 'all', 'male', 'female', 'unknown' ])->default('all');

            $table->unsignedSmallInteger('take')->default(10);

            $table->json('modules');

            $table->unsignedInteger('organisation_id')->index();
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade')->onUpdate('cascade');

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
        Schema::dropIfExists('saved_searches');
    }
}
