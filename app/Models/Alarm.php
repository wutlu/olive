<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class Alarm extends Model
{
    protected $table = 'alarms';

    protected $fillable = [
        'hit',
        'interval',
        'start_time',
        'end_time',
        'weekdays',
        'user_ids',
        'search_id',
        'sended_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
		'weekdays' => 'array',
		'user_ids' => 'array',
    ];

    protected $dates = [
        'sended_at',
    ];

    public function emails()
    {
        return User::whereIn('id', $this->user_ids)->get()->pluck('email')->toArray();
    }

    # kayıtlı arama
    public function search()
    {
        return $this->hasOne('App\Models\SavedSearch', 'id', 'search_id');
    }

    # organizasyon
    public function organisation()
    {
        return $this->hasOne('App\Models\Organisation\Organisation', 'id', 'organisation_id');
    }
}
