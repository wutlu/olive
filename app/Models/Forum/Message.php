<?php

namespace App\Models\Forum;

use Illuminate\Database\Eloquent\Model;
use Parsedown;

class Message extends Model
{
    protected $table = 'forum_messages';
    protected $fillable = [
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

    # followers
    public function followers()
    {
        return $this->hasMany('App\Models\Forum\Follow', 'message_id', 'id');
    }

    # thread (cevaplar için konu)
    public function thread()
    {
        return $this->hasOne('App\Models\Forum\Message', 'id', 'message_id');
    }

    public function markdown()
    {
        $parsedown = new Parsedown;

        return $parsedown->text($this->body);
    }

    /**
     * authority
     *
     * @return root: root yetkisi varsa erişebilir.
     * @return moderator: moderatör yetkisi varsa erişebilir.
     * @return user: kullanıcıya aitse erişebilir.
     *
     * Sırasıyla istenilen yetki alttaki yetkileri kapsar.
     */
    public function authority(bool $u = true)
    {
        if (auth()->check())
        {
            $user = auth()->user();

            if ($user->root)
            {
                return true;
            }
            elseif ($user->moderator)
            {
                return true;
            }
            elseif ($this->user_id == $user->id)
            {
                return $u;
            }
            else
            {
                return false;
            }
        }
    }
}
