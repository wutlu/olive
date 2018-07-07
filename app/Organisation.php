<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    protected $table = 'organisations';
    protected $fillable = [
		'name',
		'capacity',
		'start_date',
		'day',
		'user_id',
        'status'
    ];

    # users
    public function users()
    {
        return $this->hasMany('App\User', 'organisation_id', 'id');
    }
}
