<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
	public $incrementing = false;

    protected $table = 'logs';
	protected $primaryKey = 'uuid';
	protected $fillable = [
		'uuid',
		'module',
		'message',
		'level'
	];
}
