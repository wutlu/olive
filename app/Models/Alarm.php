<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class Alarm extends Model
{
    protected $table = 'alarms';

    protected $fillable = [
        'name',
        'hit',
        'interval',
        'start_time',
        'end_time',
        'weekdays',
        'user_ids',

        'sended_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
		'weekdays' => 'array',
		'modules' => 'array',
		'user_ids' => 'array',
    ];

    protected $dates = [
        'sended_at',
    ];

    public function emails()
    {
        return User::whereIn('id', $this->user_ids)->get()->pluck('email')->toArray();
    }
}
