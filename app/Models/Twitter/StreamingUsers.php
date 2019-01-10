<?php

namespace App\Models\Twitter;

use Illuminate\Database\Eloquent\Model;

class StreamingUsers extends Model
{
    protected $table = 'twitter_streaming_users';
    protected $fillable = [
		'screen_name',
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
}
