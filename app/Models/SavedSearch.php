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
		'category',
		'state',
		'twitter_sort',
		'twitter_sort_operator',
		'report',
		'email'
    ];

    # alarm
    public function alarm()
    {
        return $this->hasOne('App\Models\Alarm', 'id', 'alarm_id');
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'modules' => 'array',
    ];

    # organisation
    public function organisation()
    {
        return $this->hasOne('App\Models\Organisation\Organisation', 'id', 'organisation_id');
    }
}
