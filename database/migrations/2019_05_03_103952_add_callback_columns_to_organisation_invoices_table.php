<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCallbackColumnsToOrganisationInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organisation_invoices', function (Blueprint $table) {
            $table->string('reason_msg')->nullable()->default(null);
            $table->integer('reason_code')->nullable()->default(null);
            $table->integer('total_amount')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organisation_invoices', function (Blueprint $table) {
            $table->dropColumn('reason_msg');
            $table->dropColumn('reason_code');
            $table->dropColumn('total_amount');
        });
    }
}
