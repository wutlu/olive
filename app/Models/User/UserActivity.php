<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Term;

class UserActivity extends Model
{
    protected $table = 'user_activities';
    protected $fillable = [
		'key',
		'title',
		'icon',
		'markdown',
		'markdown_color',
		'button_action',
		'button_class',
		'button_text',
		'user_id',
		'push_notification'
    ];

    public function getMarkdownAttribute($value)
    {
    	return Term::markdown($value);
    }
}
