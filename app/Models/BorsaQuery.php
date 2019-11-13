<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorsaQuery extends Model
{
    protected $table = 'borsa_queries';

    protected $fillable = [
    	'name'
    ];
}
