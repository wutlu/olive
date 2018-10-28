<?php

namespace App\Models\Twitter;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'twitter_accounts';
    protected $fillable = [
		'id',
		'organisation_id',
		'token',
		'token_secret',
		'name',
		'screen_name',
		'avatar',
		'description',
		'suspended',
		'status',
		'reasons'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'token',
        'token_secret',
        'reasons'
    ];

    public $incrementing = false;
}
