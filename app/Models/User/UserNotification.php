<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $table = 'user_notifications';
    protected $fillable = [
		'user_id',
		'key'
    ];

	public $incrementing = false;
	//protected $primaryKey = 'user_id';
}
