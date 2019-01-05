<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleSearch extends Model
{
    protected $table = 'module_searches';
	protected $fillable = [
		'keyword',
		'module_id',
	];
}
