<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSystemPriceToOrganisationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->decimal('system_price', 9, 2)->default(0); // sistemin parası
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('system_price');
        });
    }
}
