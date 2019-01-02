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

            $table->string('subject')->nullable()->default(null);
            $table->longText('body');

            $table->enum('question', [ 'solved', 'unsolved', 'check' ])->nullable()->default(null); // check ise cevap doğru

            $table->boolean('closed')->default(false)->nullable()->default(null);
            $table->boolean('static')->default(false)->nullable()->default(null);

            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('updated_user_id')->index()->nullable()->default(null);
            $table->unsignedInteger('category_id')->index()->nullable()->default(null);
            $table->unsignedInteger('message_id')->index()->nullable()->default(null);
            $table->unsignedInteger('reply_id')->index()->nullable()->default(null);

            $table->unsignedInteger('hit')->>nullable()->default(null);
            $table->integer('vote')->default(0);
            $table->unsignedInteger('spam')->default(0)->unsigned();

            $table->timestamps();
        });

        Schema::table('forum_messages', function (Blueprint $table) {
            $table->foreign('updated_user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('category_id')->references('id')->on('forum_categories')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('message_id')->references('id')->on('forum_messages')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('reply_id')->references('id')->on('forum_messages')->onDelete('cascade')->onUpdate('cascade');
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
