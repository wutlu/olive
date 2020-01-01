<?php

namespace App\Models\Archive;

use Illuminate\Database\Eloquent\Model;

use App\Elasticsearch\Document;

class Pin extends Model
{
    protected $table = 'archive_items';
    protected $primaryKey = 'tmp_id';
    protected $hidden = [
        'index',
        'type',
        'id',

        'organisation_id',
        'archive_id',
        'user_id'
    ];
    protected $fillable = [
        'comment',

        'index',
        'type',
        'id',

        'archive_id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'content' => 'array'
    ];

    /**
     * authority
     *
     * @return boolean
     */
    public function authority()
    {
        $user = auth()->user();

        if ($user->root)
        {
            return true;
        }
        elseif ($this->organisation_id == $user->organisation_id)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
