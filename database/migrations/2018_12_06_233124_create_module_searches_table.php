<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModuleSearchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('module_searches', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->unsignedInteger('module_id')->index();

            $table->string('keyword')->index();

            $table->unique([ 'keyword', 'module_id' ]);

            $table->unsignedInteger('hit')->default(0);

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
        Schema::dropIfExists('module_searches');
    }
}
