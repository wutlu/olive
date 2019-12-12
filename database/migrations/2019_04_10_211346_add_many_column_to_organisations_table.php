<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddManyColumnToOrganisationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->unsignedSmallInteger('user_capacity')->default(1);
            $table->decimal('unit_price', 9, 2)->default(0);

            $table->boolean('data_twitter')->default(false);
            $table->boolean('data_sozluk')->default(false);
            $table->boolean('data_news')->default(false);
            $table->boolean('data_youtube_video')->default(false);
            $table->boolean('data_youtube_comment')->default(false);
            $table->boolean('data_shopping')->default(false);
            $table->boolean('data_facebook')->default(false);
            $table->boolean('data_instagram')->default(false);
            $table->boolean('data_blog')->default(false);

            $table->unsignedSmallInteger('pin_group_limit')->default(0);

            $table->unsignedInteger('data_pool_youtube_channel_limit')->default(0);
            $table->unsignedInteger('data_pool_youtube_video_limit')->default(0);
            $table->unsignedInteger('data_pool_youtube_keyword_limit')->default(0);
            $table->unsignedInteger('data_pool_twitter_keyword_limit')->default(0);
            $table->unsignedInteger('data_pool_twitter_user_limit')->default(0);
            $table->unsignedInteger('data_pool_facebook_keyword_limit')->default(0);
            $table->unsignedInteger('data_pool_facebook_user_limit')->default(0);

            $table->unsignedSmallInteger('historical_days')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('user_capacity');
            $table->dropColumn('unit_price');

            $table->dropColumn('data_twitter');
            $table->dropColumn('data_sozluk');
            $table->dropColumn('data_news');
            $table->dropColumn('data_youtube_video');
            $table->dropColumn('data_youtube_comment');
            $table->dropColumn('data_shopping');
            $table->dropColumn('data_facebook');
            $table->dropColumn('data_instagram');
            $table->dropColumn('data_blog');

            $table->dropColumn('pin_group_limit');

            $table->dropColumn('data_pool_youtube_channel_limit');
            $table->dropColumn('data_pool_youtube_video_limit');
            $table->dropColumn('data_pool_youtube_keyword_limit');
            $table->dropColumn('data_pool_twitter_keyword_limit');
            $table->dropColumn('data_pool_twitter_user_limit');
            $table->dropColumn('data_pool_facebook_keyword_limit');
            $table->dropColumn('data_pool_facebook_user_limit');

            $table->dropColumn('historical_days');
        });
    }
}
