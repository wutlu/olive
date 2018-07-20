<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationDiscountCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organisation_discount_coupons', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('key')->default(0)->index();

            $table->unsignedSmallInteger('rate')->default(0);
            $table->unsignedInteger('price')->default(0);

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
        Schema::dropIfExists('organisation_discounts');
    }
}
