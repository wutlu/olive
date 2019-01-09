<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->unique();

            $table->unsignedInteger('user_id')->nullable()->index();
            $table->unsignedInteger('ping')->default(0);

            $table->ipAddress('ip_address')->nullable()->default(null);
            $table->text('user_agent')->nullable()->default(null);

            $table->text('payload');

            $table->string('page')->nullable()->default(null);
            $table->string('referer')->nullable()->default(null);

            $table->string('device')->nullable()->default(null);
            $table->string('browser')->nullable()->default(null);
            $table->string('platform')->nullable()->default(null);
            $table->string('robot')->nullable()->default(null);

            $table->integer('last_activity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sessions');
    }
}
