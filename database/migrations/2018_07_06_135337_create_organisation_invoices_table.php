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
            $table->increments('id')->unsigned();

            $table->unsignedInteger('organisation_id')->nullable()->default(null);
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');

            $table->unsignedInteger('user_id')->nullable()->default(null);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedInteger('invoice_id')->unique();

            $table->string('name');
            $table->string('lastname');
            $table->string('address');

            $table->json('json');
            $table->text('notes')->nullable()->default(null);

            $table->decimal('unit_price', 9, 3)->default(0);
            $table->decimal('total_price', 9, 3)->default(0);
            $table->decimal('discount', 9, 3)->default(0);
            $table->decimal('tax', 9, 3)->default(0);

            $table->boolean('paid')->default(0);
            $table->string('payment_notification')->nullable()->default(null);

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
