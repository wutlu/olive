<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $table = 'user_badges';
    protected $fillable = [
    	'badge_id',
    	'user_id'
    ];
}
