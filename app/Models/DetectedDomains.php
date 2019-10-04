<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetectedDomains extends Model
{
    protected $table = 'detected_domains';

    public function color()
    {
    	switch ($this->status)
    	{
    		case 'new':
    			$color = 'blue';
    		break;
    		case 'err':
    			$color = 'red';
    		break;
    		case 'ok':
    			$color = 'green';
    		break;
    	}

    	return $color;
    }
}
