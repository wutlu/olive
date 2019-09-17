<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PopTrend extends Model
{
    protected $table = 'popular_trends';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'details' => 'array',
        'ranks' => 'array'
    ];
}
