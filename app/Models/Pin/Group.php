<?php

namespace App\Models\Pin;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'pin_groups';
    protected $fillable = [
    	'name'
    ];

    # pins
    public function pins()
    {
        return $this->hasMany('App\Models\Pin\Pin', 'group_id', 'id');
    }

    # organisation
    public function organisation()
    {
        return $this->hasOne('App\Models\Organisation\Organisation', 'id', 'organisation_id');
    }
}
