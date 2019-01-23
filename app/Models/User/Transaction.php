<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'user_transactions';

    # user
    public function user()
    {
        return $this->hasOne('App\Models\User\User', 'id', 'user_id');
    }
}
