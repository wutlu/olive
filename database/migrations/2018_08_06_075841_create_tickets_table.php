<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->unsignedInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('invoice_id')->nullable()->default(null)->index();
            $table->foreign('invoice_id')->references('invoice_id')->on('organisation_invoices')->onDelete('SET NULL')->onUpdate('cascade');

            $table->unsignedInteger('ticket_id')->nullable()->default(null)->index();
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade')->onUpdate('cascade');

            $table->string('subject')->nullable()->default(null);
            $table->text('message');
            $table->string('type')->nullable()->default(null);

            $table->enum('status', [ 'closed', 'open' ])->nullable()->default(null);

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
        Schema::dropIfExists('tickets');
    }
}
