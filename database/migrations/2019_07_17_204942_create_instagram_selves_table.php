<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstagramSelvesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instagram_selves', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->string('url');

            $table->unsignedBigInteger('hit')->default(0);

            $table->enum('method', [ 'user', 'hashtag', 'location' ]);

            $table->unsignedSmallInteger('control_interval')->default(10);
            $table->timestamp('control_date')->default(\DB::raw('now()'));

            $table->boolean('status')->default(0);
            $table->string('reason')->nullable()->default(null);
            $table->unsignedSmallInteger('error_count')->default(0);

            $table->unsignedInteger('organisation_id')->index();
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade')->onUpdate('cascade');

            $table->unique([ 'url', 'organisation_id' ]);

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
        Schema::dropIfExists('instagram_selves');
    }
}
