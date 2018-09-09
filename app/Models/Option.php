<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $table = 'options';
	public $incrementing = false;
	protected $primaryKey = 'key';
	protected $fillable = [
		'key',
		'value'
	];

    # increment
    public function incr(int $value = 1)
    {
        return $this->update([ 'value' => $this->value + $value ]);
    }

    # decrement
    public function decr(int $value = 1)
    {
        return $this->update([ 'value' => $this->value > $value ? $this->value - $value : 0 ]);
    }
}
