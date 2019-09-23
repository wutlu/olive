<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedSearch extends Model
{
    protected $table = 'saved_searches';

    protected $fillable = [
		'name',
		'string',
		'illegal',
		'reverse',
		'sentiment_pos',
		'sentiment_neu',
		'sentiment_neg',
		'sentiment_hte',
		'consumer_que',
		'consumer_req',
		'consumer_cmp',
		'consumer_nws',
		'gender',
		'take',
		'modules',
		'sharp',
		'categories'
    ];
}
