<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnalysisToolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('analysis_tools', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('platform'); // youtube, instagram, twitter

            $table->string('social_id')->index();
            $table->string('social_title')->nullable()->default(null);

            $table->json('analysis_user')->nullable()->default(null);
            $table->json('analysis_data')->nullable()->default(null);

            $table->unsignedInteger('organisation_id')->index();
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade')->onUpdate('cascade');

            $table->unique([ 'social_id', 'platform', 'organisation_id' ]);

            $table->unsignedSmallInteger('error_count')->default(0);
            $table->string('reason')->nullable()->default(null);

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
        Schema::dropIfExists('analysis_tools');
    }
}
