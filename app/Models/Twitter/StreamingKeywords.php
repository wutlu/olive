<?php

namespace App\Models\Twitter;

use Illuminate\Database\Eloquent\Model;

class StreamingKeywords extends Model
{
    protected $table = 'twitter_streaming_keywords';
    protected $fillable = [
		'keyword',
		'reasons',
		'status'
    ];
}
