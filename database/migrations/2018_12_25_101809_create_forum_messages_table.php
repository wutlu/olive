<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forum_messages', function (Blueprint $table) {
            $table->increments('id')->unisigned();

            $table->string('subject');
            $table->longText('body');

            $table->enum('question', [ 'solved', 'unsolved', 'check' ])->nullable()->default(null); // check ise cevap doğru

            $table->boolean('lock')->default(false);
            $table->boolean('static')->default(false);

            $table->unsignedInteger('category_id')->index();
            $table->unsignedInteger('message_id')->nullable()->default(null)->index();

            $table->integer('hit')->default(0)->unsigned();
            $table->integer('vote')->default(0);
            $table->integer('spam')->default(0)->unsigned();

            $table->timestamps();
        });

        Schema::table('forum_messages', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('forum_categories')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('message_id')->references('id')->on('forum_messages')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('forum_messages');
    }
}
