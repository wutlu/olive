<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('key')->unique();

            $table->string('title');
            $table->string('icon')->default('info');
            $table->text('markdown')->nullable()->default(null);
            $table->string('markdown_color')->nullable()->default(null);

            $table->string('button_action')->nullable()->default(null);
            $table->string('button_class')->nullable()->default(null);
            $table->string('button_text')->nullable()->default(null);

            $table->enum('push_notification', [ 'on', 'ok', 'off' ])->default('off');

            $table->unsignedInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

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
        Schema::dropIfExists('user_activities');
    }
}
