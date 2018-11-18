<?php

namespace App\Models\Twitter;

use Illuminate\Database\Eloquent\Model;

class StreamingUsers extends Model
{
    protected $table = 'twitter_streaming_users';
    protected $fillable = [
		'screen_name',
		'user_id',
		'reasons',
		'status'
    ];

    # organizasyon
    public function organisation()
    {
        return $this->hasOne('App\Models\Organisation\Organisation', 'id', 'organisation_id');
    }
}
