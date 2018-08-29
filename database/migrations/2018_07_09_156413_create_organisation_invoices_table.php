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
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedInteger('user_id')->nullable()->default(null);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->decimal('unit_price', 9, 2)->default(0);
            $table->unsignedSmallInteger('month')->default(1);
            $table->decimal('total_price', 9, 2)->default(0);

            $table->unsignedSmallInteger('tax')->default(0);

            $table->json('plan');

            $table->timestamp('paid_at')->nullable()->default(null);

            $table->string('no')->nullable()->default(null);
            $table->string('serial')->nullable()->default(null);

            $table->unsignedInteger('billing_information_id');
            $table->foreign('billing_information_id')->references('id')->on('billing_informations')->onDelete('cascade')->onUpdate('cascade');

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
