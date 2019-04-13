<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddModulesToOrganisationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->boolean('module_real_time')->default(0);
            $table->boolean('module_search')->default(0);
            $table->boolean('module_trend')->default(0);
            $table->boolean('module_alarm')->default(0);
            $table->boolean('module_pin')->default(0);
            $table->boolean('module_model')->default(0);
            $table->boolean('module_forum')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('module_real_time');
            $table->dropColumn('module_search');
            $table->dropColumn('module_trend');
            $table->dropColumn('module_alarm');
            $table->dropColumn('module_pin');
            $table->dropColumn('module_model');
            $table->dropColumn('module_forum');
        });
    }
}
