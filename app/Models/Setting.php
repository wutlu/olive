<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
	public $incrementing = false;
	protected $primaryKey = 'key';
	protected $fillable = [
		'key',
		'value'
	];
}
