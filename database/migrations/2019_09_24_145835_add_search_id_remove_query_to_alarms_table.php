<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSearchIdRemoveQueryToAlarmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alarms', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('query');
            $table->dropColumn('modules');

            $table->unsignedInteger('search_id')->index();
            $table->foreign('search_id')->references('id')->on('saved_searches')->onDelete('cascade')->onUpdate('cascade');

            $table->unique([ 'search_id', 'organisation_id' ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alarms', function (Blueprint $table) {
            $table->string('name');
            $table->text('query');
            $table->json('modules');

            $table->dropColumn('search_id');
        });
    }
}
