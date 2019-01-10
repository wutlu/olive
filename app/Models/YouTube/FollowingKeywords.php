<?php

namespace App\Models\YouTube;

use Illuminate\Database\Eloquent\Model;

class FollowingKeywords extends Model
{
    protected $table = 'youtube_following_keywords';
    protected $fillable = [
        'keyword',
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
