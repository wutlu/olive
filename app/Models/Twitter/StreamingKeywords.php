<?php

namespace App\Models\Twitter;

use Illuminate\Database\Eloquent\Model;

class StreamingKeywords extends Model
{
    protected $table = 'twitter_streaming_keywords';
    protected $fillable = [
		'keyword',
		'reason'
    ];

    # organizasyon
    public function organisation()
    {
        return $this->hasOne('App\Models\Organisation\Organisation', 'id', 'organisation_id');
    }
}
