<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewslettersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('newsletters', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('subject');
            $table->text('body');

            $table->text('email_list')->nullable()->default(null);
            $table->unsignedInteger('sent_line')->default(0);

            $table->enum('status', [ 'process', 'ok', 'triggered' ])->nullable()->default(null);

            $table->timestamp('send_date');

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
        Schema::dropIfExists('newsletters');
    }
}
