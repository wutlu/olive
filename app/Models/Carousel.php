<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carousel extends Model
{
    protected $table = 'carousels';
    protected $fillable = [
		'title',
		'description',
		'pattern',

		'button_action',
		'button_text',

		'visibility'
    ];
}
