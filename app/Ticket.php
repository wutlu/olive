<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'tickets';

    protected $fillable = [
		'invoice_id',
		'ticket_id',
		'subject',
		'message',
		'type'
    ];

    # verilen cevaplar
    public function replies()
    {
        return $this->hasMany('App\Ticket', 'ticket_id', 'id')->orderBy('updated_at', 'ASC');
    }
}
