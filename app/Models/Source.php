<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    protected $table = 'sources';
    protected $fillable = [
    	'name',
    	'source_media',
    	'source_sozluk',
    	'source_blog',
    	'source_forum',
    	'source_shopping'
    ];

    /**
     * The attributes that should be mutated.
     *
     * @var array
     */
    protected $casts = [
    	'source_media' => 'array',
    	'source_sozluk' => 'array',
    	'source_blog' => 'array',
    	'source_forum' => 'array',
    	'source_shopping' => 'array'
    ];
}
