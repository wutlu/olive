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

    /**
     * authority
     *
     * @return boolean
     */
    public function authority()
    {
        $user = auth()->user();

        if ($user->root)
        {
            return true;
        }
        elseif ($this->organisation_id == $user->organisation_id)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
