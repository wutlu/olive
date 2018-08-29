<?php

namespace App\Models;

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
        return $this->hasMany('App\Models\Ticket', 'ticket_id', 'id')->orderBy('updated_at', 'ASC');
    }

    # user
    public function user()
    {
        return $this->hasOne('App\Models\User\User', 'id', 'user_id');
    }

    # fatura
    public function invoice()
    {
        return $this->hasOne('App\Models\Organisation\OrganisationInvoice', 'invoice_id', 'invoice_id');
    }
}
