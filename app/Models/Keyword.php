<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    protected $table = 'keywords';
    protected $fillable = [
    	'keyword'
    ];

    # user
    public function user()
    {
        return $this->hasOne('App\Models\User\User', 'id', 'user_id');
    }

    # organisation
    public function organisation()
    {
        return $this->hasOne('App\Models\Organisation\Organisation', 'id', 'organisation_id');
    }
}
