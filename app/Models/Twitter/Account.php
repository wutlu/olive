<?php

namespace App\Models\Twitter;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'twitter_accounts';
	public $incrementing = false;
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
}
