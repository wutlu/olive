<?php

namespace App\Models\RealTime;

use Illuminate\Database\Eloquent\Model;

class KeywordGroup extends Model
{
    protected $table = 'real_time_keyword_groups';
    protected $fillable = [
    	'name',
    	'keywords',
		'module_youtube_video',
		'module_youtube_comment',
		'module_twitter',
		'module_sozluk',
		'module_news',
		'module_shopping',
    ];
}
