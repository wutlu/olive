<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $table = 'options';
	public $incrementing = false;
	protected $primaryKey = 'key';
	protected $fillable = [
		'key',
		'value'
	];
}
