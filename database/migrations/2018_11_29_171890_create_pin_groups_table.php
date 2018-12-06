<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePinGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pin_groups', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('name')->index();

            $table->unsignedInteger('organisation_id')->index();
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade')->onUpdate('cascade');

            $table->enum('html_to_pdf', [ 'process', 'success' ])->nullable()->default(null);
            $table->string('html_path')->nullable()->default(null);
            $table->string('pdf_path')->nullable()->default(null);
            $table->timestamp('completed_at')->nullable()->default(null);

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
        Schema::dropIfExists('pin_groups');
    }
}
