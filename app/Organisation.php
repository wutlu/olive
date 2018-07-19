<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organisation extends Model
{
    use SoftDeletes;

    protected $table = 'organisations';
    protected $fillable = [
		'name',
		'capacity',
		'start_date',
		'day',
		'user_id',
        'status'
    ];

    protected $dates = [ 'deleted_at' ];

    # users
    public function users()
    {
        return $this->hasMany('App\User', 'organisation_id', 'id');
    }
}
