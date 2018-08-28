<?php

namespace App\Models\Crawlers;

use Illuminate\Database\Eloquent\Model;

class MediaCrawler extends Model
{
    protected $table = 'media_crawlers';
    protected $fillable = [
        'name',
        'site',
        'base',
        'url_pattern',
        'selector_title',
        'selector_description',

        'off_limit',
        'control_interval'
    ];
}
