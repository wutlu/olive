<?php

namespace App\Models\RealTime;

use Illuminate\Database\Eloquent\Model;

use App\Elasticsearch\Document;

class Pin extends Model
{
    protected $table = 'real_time_pins';
    public $incrementing = false;
    protected $hidden = [
        'index',
        'type',
        'id',

        'organisation_id',
        'group_id',
        'user_id'
    ];
    protected $fillable = [
        'comment',

        'index',
        'type',
        'id',

        'group_id'
    ];

    public function document(int $id = 0)
    {
        if ($id)
        {
            return @Document::list([ 'twitter', 'tweets', '*' ], 'tweet', [ 'query' => [ 'match' => [ 'id' => $id ] ] ])->data['hits']['hits'][0];
        }
        else
        {
            return Document::get($this->index, $this->type, $this->id);
        }
    }
}
