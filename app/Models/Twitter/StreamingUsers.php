<?php

namespace App\Models\Twitter;

use Illuminate\Database\Eloquent\Model;

class StreamingUsers extends Model
{
    protected $table = 'twitter_streaming_users';
    protected $fillable = [
        'user_id',
        'organisation_id',
		'screen_name',
        'verified',
		'reason'
    ];

    # organizasyon
    public function organisation()
    {
        return $this->hasOne('App\Models\Organisation\Organisation', 'id', 'organisation_id');
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'organisation_id',
    ];

    public function getUserIdAttribute($value)
    {
        return ''.$value.'';
    }
}
