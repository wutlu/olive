<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'session_id',

        'skip_intro',
        'organisation_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token'
    ];

    # avatar
    public function avatar()
    {
        return $this->avatar ? $this->avatar : asset('img/people.svg');
    }

    # organizasyon
    public function organisation()
    {
        return $this->hasOne('App\Organisation', 'id', 'organisation_id');
    }

    # ödeme bilgileri
    public function billingInformations(bool $protected = true)
    {
        return $this->hasMany('App\BillingInformation', 'user_id', 'id')->where('protected', $protected)->get();
    }

    # destek talepleri
    public function tickets(int $pager = 5)
    {
        return $this->hasMany('App\Ticket', 'user_id', 'id')->whereNull('ticket_id')->orderBy('updated_at', 'DESC')->paginate($pager);
    }
}
