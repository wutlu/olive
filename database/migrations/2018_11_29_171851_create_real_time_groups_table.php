<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRealTimeGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('real_time_groups', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('name')->index();

            $table->unsignedInteger('organisation_id')->index();
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade')->onUpdate('cascade');

            $table->timestamp('pdf_completed_at')->nullable()->default(null);

            $table->boolean('module_youtube')->default(0);
            $table->boolean('module_twitter')->default(0);
            $table->boolean('module_sozluk')->default(0);
            $table->boolean('module_news')->default(0);
            $table->boolean('module_shopping')->default(0);

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
        Schema::dropIfExists('real_time_groups');
    }
}
