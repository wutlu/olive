<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('name');

            $table->unsignedInteger('organisation_id')->nullable()->default(null)->index();
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade')->onUpdate('cascade');

            $table->json('source_media')->nullable()->default(null);
            $table->json('source_sozluk')->nullable()->default(null);
            $table->json('source_blog')->nullable()->default(null);
            $table->json('source_forum')->nullable()->default(null);
            $table->json('source_shopping')->nullable()->default(null);

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
        Schema::dropIfExists('sources');
    }
}
