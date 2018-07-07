<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Utilities\Term;
use Parsedown;

class UserActivity extends Model
{
    protected $table = 'user_activities';
    protected $fillable = [
		'key',
		'title',
		'icon',
		'markdown',
		'markdown_color',
		'button_type',
		'button_method',
		'button_action',
		'button_class',
		'button_text',
		'user_id',
    ];

    public function getMarkdownAttribute($value)
    {
    	$parsedown = new Parsedown;

    	return $parsedown->text($value);
    }
}
