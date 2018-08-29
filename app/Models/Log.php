<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';
	public $incrementing = false;
	protected $primaryKey = 'uuid';
	protected $fillable = [
		'uuid',
		'module',
		'message',
		'level'
	];
}
