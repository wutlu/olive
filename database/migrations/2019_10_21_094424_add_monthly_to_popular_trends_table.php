<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMonthlyToPopularTrendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('popular_trends', function (Blueprint $table) {
            $table->dropUnique([ 'module', 'social_id' ]);

            $table->string('month_key')->index();
            $table->string('category')->index()->nullable()->default(null);
            $table->unsignedInteger('followers')->default(0);

            $table->unique([ 'module', 'social_id', 'month_key' ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('popular_trends', function (Blueprint $table) {
            $table->unique([ 'module', 'social_id' ]);
            $table->dropUnique([ 'module', 'social_id', 'month_key' ]);

            $table->dropColumn('month_key');
            $table->dropColumn('category');
            $table->dropColumn('followers');
        });
    }
}
