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
		'status',
		'user_id'
    ];

    # users
    public function users()
    {
        return $this->hasMany('App\User', 'organisation_id', 'id');
    }
}
