<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBorsaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('borsa_history', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();

            $table->string('name')->index();
            $table->string('group')->index();
            $table->date('date');

            $table->unique([
                'name',
                'group',
                'date'
            ]);

            $table->time('hour');

            $table->decimal('value', 9, 2)->default(0);
            $table->decimal('buy', 9, 2)->default(0);
            $table->decimal('sell', 9, 2)->default(0);
            $table->decimal('diff', 9, 2)->default(0);
            $table->decimal('max', 9, 2)->default(0);
            $table->decimal('min', 9, 2)->default(0);

            $table->decimal('lot', 20, 2)->default(0);
            $table->decimal('tl', 20, 2)->default(0);

            $table->unsignedInteger('total_pos')->nullable()->default(null);
            $table->unsignedInteger('total_neg')->nullable()->default(null);
            $table->unsignedInteger('pos_neg')->nullable()->default(null);

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
        Schema::dropIfExists('borsa_history');
    }
}
