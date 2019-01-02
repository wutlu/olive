<?php

namespace App\Models\Forum;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    protected $table = 'forum_follows';
    protected $fillable = [
    	'user_id',
    	'message_id'
    ];
}
