<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSendedAtToAlarmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alarms', function (Blueprint $table) {
            $table->timestamp('sended_at')->default(\DB::raw('now()'));
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
            $table->dropColumn('sended_at');
        });
    }
}
