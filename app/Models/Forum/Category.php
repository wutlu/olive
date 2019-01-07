<?php

namespace App\Models\Forum;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'forum_categories';
    protected $fillable = [
    	'name',
    	'slug',
    	'description'
    ];

    # kategori içerisinde açılan konular
    public function threads()
    {
        return $this->hasMany('App\Models\Forum\Message', 'category_id', 'id')->whereNull('message_id');
    }
}
