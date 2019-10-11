<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSmsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('gsm')->nullable()->default(null)->unique();
            $table->string('gsm_code')->nullable()->default(null);
            $table->timestamp('gsm_verified_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('gsm');
            $table->dropColumn('gsm_code');
            $table->dropColumn('gsm_verified_at');
        });
    }
}
