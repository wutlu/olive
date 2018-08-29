<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingInformationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_informations', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->enum('type', [ 'individual', 'corporate', 'person' ])->default('individual');

            $table->string('person_name')->nullable()->default(null);
            $table->string('person_lastname')->nullable()->default(null);
            $table->unsignedBigInteger('person_tckn')->nullable()->default(null);

            $table->string('merchant_name')->nullable()->default(null);
            $table->unsignedBigInteger('tax_number')->nullable()->default(null);
            $table->string('tax_office')->nullable()->default(null);

            $table->unsignedInteger('country_id');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedInteger('state_id');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->string('city');
            $table->string('address');
            $table->unsignedInteger('postal_code');

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
        Schema::dropIfExists('billing_informations');
    }
}
