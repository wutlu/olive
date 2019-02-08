<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvoiceIdToOrganisationInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('discount_coupons', function (Blueprint $table) {
            $table->unsignedBigInteger('invoice_id')->nullable()->default(null)->index();
            $table->foreign('invoice_id')->references('invoice_id')->on('organisation_invoices')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('discount_coupons', function (Blueprint $table) {
            $table->dropColumn('invoice_id');
        });
    }
}
