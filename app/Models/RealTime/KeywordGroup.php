<?php

namespace App\Models\RealTime;

use Illuminate\Database\Eloquent\Model;

class KeywordGroup extends Model
{
    protected $table = 'real_time_keyword_groups';
    protected $fillable = [
    	'name',
    	'keywords'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
		'modules' => 'array',
    ];
}
