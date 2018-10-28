<?php

namespace App\Models\Twitter;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $table = 'twitter_tokens';
    protected $fillable = [
		'consumer_key',
		'consumer_secret',
		'access_token',
		'access_token_secret',
		'off_limit'
	];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function getPidAttribute($pid)
    {
    	$pid_term = posix_getpgid($pid);

        return $pid ? ($pid_term ? $pid : false) : null;
    }
}
