<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportedContents extends Model
{
    protected $table = 'reported_contents';

    protected $fillable = [
		'_id',
		'_type',
		'_index',
		'sentiment',
		'consumer',
		'user_id',
    ];

    # user
    public function user()
    {
        return $this->hasOne('App\Models\User\User', 'id', 'user_id');
    }
}
