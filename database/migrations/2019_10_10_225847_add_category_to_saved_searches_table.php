<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCategoryToSavedSearchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('saved_searches', function (Blueprint $table) {
            $table->dropColumn('categories');
            $table->string('category')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('saved_searches', function (Blueprint $table) {
            $table->json('categories')->nullable()->default(null);
            $table->dropColumn('category');
        });
    }
}
