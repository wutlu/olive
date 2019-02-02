<?php

namespace App\Models\Crawlers;

use Illuminate\Database\Eloquent\Model;

class Host extends Model
{
    protected $table = 'hosts';

    protected $fillable = [
		'site',
		'ip_address'
    ];
}
