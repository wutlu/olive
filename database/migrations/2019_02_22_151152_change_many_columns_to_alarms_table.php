<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeManyColumnsToAlarmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alarms', function (Blueprint $table) {
            $table->json('weekdays')->default('{}')->change();
            $table->json('modules')->default('{}')->change();
            $table->json('emails')->default('{}')->change();
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
            $table->json('weekdays')->change();
            $table->json('modules')->change();
            $table->json('emails')->change();
        });
    }
}
