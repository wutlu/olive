<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Analysis extends Model
{
    protected $table = 'analysis';

    protected $fillable = [
    	'word',
    	'compiled'
    ];
}
