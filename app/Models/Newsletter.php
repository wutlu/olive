<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $table = 'newsletters';
    protected $fillable = [
    	'status',
    	'sent_line'
    ];
}
