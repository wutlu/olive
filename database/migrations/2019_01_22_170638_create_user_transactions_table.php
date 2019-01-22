<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_transactions', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->decimal('price', 9, 2);
            $table->string('currency');

            $table->enum('withdraw', [ 'wait', 'success', 'failed' ])->nullable()->default(null);

            $table->string('status_message')->nullable()->default(null);

            $table->string('iban')->nullable()->default(null);
            $table->string('iban_name')->nullable()->default(null);

            $table->unsignedInteger('user_id')->index();
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
        Schema::dropIfExists('user_transactions');
    }
}
