<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TwitterUsers extends Model
{
    protected $table = 'twitter_users';
	protected $fillable = [
		'user_id',
		'token',
		'token_secret',
		'nickname',
		'name',
		'avatar',
		'verified',
		'status',
		'organisation_id'
	];
}
