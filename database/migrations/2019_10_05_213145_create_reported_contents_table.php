<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportedContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reported_contents', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('_id');
            $table->string('_type');
            $table->string('_index');

            $table->unique([ '_id', '_type', '_index' ]);

            $table->enum('sentiment', [ 'pos', 'neu', 'neg', 'hte' ])->nullable()->default(null);
            $table->enum('consumer', [ 'que', 'req', 'nws', 'cmp' ])->nullable()->default(null);

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
        Schema::dropIfExists('reported_contents');
    }
}
