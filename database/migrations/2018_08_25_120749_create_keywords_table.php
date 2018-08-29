<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keywords', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('keyword')->index();

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedInteger('organisation_id');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade')->onUpdate('cascade');

            $table->unique([ 'keyword', 'organisation_id' ]);

            $table->boolean('status')->default(0);

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
        Schema::dropIfExists('keywords');
    }
}
