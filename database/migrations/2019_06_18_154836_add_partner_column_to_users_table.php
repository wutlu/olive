<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPartnerColumnToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('partner', [ 'eagle', 'phoenix', 'gryphon', 'dragon' ])->nullable()->default(null);

            $table->unsignedInteger('partner_user_id')->nullable()->default(null)->index();
            $table->foreign('partner_user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
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
            $table->dropColumn('partner');
            $table->dropColumn('partner_user_id');
        });
    }
}
