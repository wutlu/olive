<?php

namespace App\Models\Pin;

use Illuminate\Database\Eloquent\Model;

use App\Elasticsearch\Document;

class Pin extends Model
{
    protected $table = 'pins';
    protected $primaryKey = 'tmp_id';
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
            $document = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', [ 'query' => [ 'match' => [ 'id' => $id ] ] ]);

            return @$document->data['hits']['hits'][0];
        }
        else
        {
            return Document::get($this->index, $this->type, $this->id);
        }
    }

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
