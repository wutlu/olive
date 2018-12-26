<?php

namespace App\Models\Forum;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'forum_messages';
    protected $filable = [
    	'subject',
    	'body',
    	'question'
    ];

    # user
    public function route()
    {
        return route('forum.thread', [
        	'id' => $this->id,
        	'slug' => $this->category->slug,
        	'fake_slug' => str_slug($this->subject)
        ]);
    }

    # user
    public function user()
    {
        return $this->hasOne('App\Models\User\User', 'id', 'user_id');
    }

    # category
    public function category()
    {
        return $this->hasOne('App\Models\Forum\Category', 'id', 'category_id');
    }

    # replies
    public function replies()
    {
        return $this->hasMany('App\Models\Forum\Message', 'message_id', 'id')->orderBy('created_at', 'ASC');
    }
}
