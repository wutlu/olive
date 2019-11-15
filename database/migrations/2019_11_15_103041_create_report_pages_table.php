<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_pages', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();

            $table->unsignedInteger('report_id')->index();
            $table->foreign('report_id')->references('id')->on('reports')->onDelete('cascade')->onUpdate('cascade');

            $table->string('title');
            $table->string('subtitle')->nullable()->default(null);
            $table->text('text')->nullable()->default(null);
            $table->string('image')->nullable()->default(null);

            $table->unsignedSmallInteger('sort')->default(0);

            $table->json('data')->nullable()->default(null);
            $table->string('data_type')->nullable()->default(null);

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
        Schema::dropIfExists('report_pages');
    }
}
