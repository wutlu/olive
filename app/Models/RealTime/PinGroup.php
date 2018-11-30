<?php

namespace App\Models\RealTime;

use Illuminate\Database\Eloquent\Model;

class PinGroup extends Model
{
    protected $table = 'real_time_pin_groups';
    protected $fillable = [
    	'name'
    ];

    # pins
    public function pins()
    {
        return $this->hasMany('App\Models\RealTime\Pin', 'group_id', 'id');
    }
}
