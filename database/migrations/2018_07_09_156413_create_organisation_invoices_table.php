<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organisation_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('invoice_id')->unique();

            $table->unsignedInteger('organisation_id')->nullable()->default(null);
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');

            $table->unsignedInteger('user_id')->nullable()->default(null);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedInteger('billing_information_id')->nullable()->default(null);
            $table->foreign('billing_information_id')->references('id')->on('billing_informations')->onDelete('cascade');

            $table->decimal('unit_price', 9, 3)->default(0);
            $table->unsignedSmallInteger('month')->default(1);
            $table->decimal('total_price', 9, 3)->default(0);
            $table->decimal('amount_of_tax', 9, 3)->default(0);

            $table->json('discount')->nullable()->default(null);
            $table->json('pay_notice')->nullable()->default(null);

            $table->boolean('pay_confirmed')->default(0);

            $table->json('plan');

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
        Schema::dropIfExists('organisation_invoices');
    }
}
