<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropManyColumnsToRealTimeKeywordGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('real_time_keyword_groups', function (Blueprint $table) {
            $table->dropColumn('module_youtube_video');
            $table->dropColumn('module_youtube_comment');
            $table->dropColumn('module_twitter');
            $table->dropColumn('module_sozluk');
            $table->dropColumn('module_news');
            $table->dropColumn('module_shopping');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('real_time_keyword_groups', function (Blueprint $table) {
            $table->boolean('module_youtube_video')->default(0);
            $table->boolean('module_youtube_comment')->default(0);
            $table->boolean('module_twitter')->default(0);
            $table->boolean('module_sozluk')->default(0);
            $table->boolean('module_news')->default(0);
            $table->boolean('module_shopping')->default(0);
        });
    }
}
