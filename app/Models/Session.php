<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $table = 'sessions';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'uuid';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'browser' => 'array',
        'os' => 'array',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'payload'
    ];

    # user
    public function user()
    {
        return $this->hasOne('App\Models\User\User', 'id', 'user_id');
    }
}
