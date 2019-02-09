<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Term;

class Page extends Model
{
    protected $table = 'pages';
    protected $fillable = [
		'title',
		'slug',
		'keywords',
		'description',
		'body'
    ];

    public function markdown()
    {
        return Term::markdown($this->body);
    }
}
