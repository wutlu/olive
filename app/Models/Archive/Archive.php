<?php

namespace App\Models\Archive;

use Illuminate\Database\Eloquent\Model;

class Archive extends Model
{
    protected $table = 'archives';
    protected $fillable = [
    	'name'
    ];

    # items
    public function items()
    {
        return $this->hasMany('App\Models\Archive\Item', 'archive_id', 'id');
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
