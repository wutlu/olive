<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Borsa extends Model
{
    protected $table = 'borsa_history';

    protected $fillable = [
    	'name'
    ];
}
