<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Utilities\Term;

class Carousel extends Model
{
    protected $table = 'carousels';
    protected $fillable = [
		'title',
		'description',
		'pattern',

		'button_action',
		'button_text',

		'carousel',
		'modal',
    ];

    public function markdown()
    {
    	return Term::markdown($this->description);
    }
}
