<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBorsaQueriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('borsa_queries', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();

            $table->string('query_pos')->nullable()->default(null);
            $table->string('query_neg')->nullable()->default(null);

            $table->string('name')->unique();

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
        Schema::dropIfExists('borsa_queries');
    }
}
