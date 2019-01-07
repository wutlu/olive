<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserNewslettersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_newsletters', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('subject');
            $table->text('body');

            $table->json('mail_list')->nullable()->default(null);
            $table->json('sent_list')->nullable()->default(null);

            $table->enum('status', [ 'process', 'failed', 'ok' ])->nullable()->default(null);

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
        Schema::dropIfExists('user_newsletters');
    }
}
