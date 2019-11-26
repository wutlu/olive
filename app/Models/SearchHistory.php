<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SearchHistory extends Model
{
    use SoftDeletes;

    protected $table = 'search_histories';

    protected $fillable = [
        'query'
    ];

    protected $softDelete = true;
}
