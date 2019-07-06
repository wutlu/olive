<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartnerPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_payments', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('currency');
            $table->decimal('amount', 9, 2)->default(0);

            $table->enum('status', [ 'pending', 'success', 'cancelled' ])->default('pending');

            $table->string('message')->nullable()->default(null);

            $table->unsignedInteger('user_id')->nullable()->default(null)->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

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
        Schema::dropIfExists('partner_payments');
    }
}
