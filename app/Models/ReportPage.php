<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Utilities\Term;

class ReportPage extends Model
{
    protected $table = 'report_pages';

    protected $fillable = [
    	'title',
    	'subtitle',
    	'text'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
		'data' => 'array'
    ];

    public function markdown()
    {
        return Term::markdown($this->text);
    }

    public function pattern()
    {
        $this->text = Term::markdown($this->text);

        $data = $this->data;

        unset($this->data);

        if ($this->type == 'data.stats')
        {
            return json_encode(
                [
                    'page' => $this,
                    'stats' => $data
                ]
            );
        }
        else
        {
            return json_encode(
                [
                    'page' => $this,
                    'data' => $data
                ]
            );
        }
    }

    # rapor
    public function report()
    {
        return $this->hasOne('App\Models\Report', 'id', 'report_id');
    }
}
