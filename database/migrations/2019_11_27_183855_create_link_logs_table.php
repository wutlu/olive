<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinkLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('link_logs', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->ipAddress('ip_address');

            $table->text('user_agent')->nullable()->default(null);

            $table->string('referer', 1024)->nullable()->default(null);

            $table->boolean('is_mobile')->default(false);
            $table->boolean('is_tablet')->default(false);
            $table->boolean('is_desktop')->default(false);
            $table->boolean('is_phone')->default(false);

            $table->string('device')->nullable()->default(null); // iPhone, Nexus, AsusTablet, ...

            $table->json('browser');
            $table->json('os');

            $table->string('robot')->nullable()->default(null);

            $table->string('short')->nullable()->default(null)->index();

            $table->unsignedInteger('link_id')->index();
            $table->foreign('link_id')->references('id')->on('links')->onDelete('cascade')->onUpdate('cascade');

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
        Schema::dropIfExists('link_logs');
    }
}
