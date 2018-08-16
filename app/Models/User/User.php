<?php

namespace App\Models\User;

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
        'organisation_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'session_id',
        'organisation_id',
        'root'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'root' => 'boolean'
    ];

    # avatar
    public function avatar()
    {
        return asset($this->avatar ? $this->avatar : 'img/people.svg');
    }

    # organizasyon
    public function organisation()
    {
        return $this->hasOne('App\Models\Organisation\Organisation', 'id', 'organisation_id');
    }

    # ödeme bilgileri
    public function billingInformations()
    {
        return $this->hasMany('App\Models\Organisation\BillingInformation', 'user_id', 'id');
    }

    # faturalar
    public function invoices()
    {
        return $this->hasMany('App\Models\Organisation\OrganisationInvoice', 'user_id', 'id');
    }

    # destek talepleri
    public function tickets(int $pager = 5)
    {
        return $this->hasMany('App\Models\Ticket', 'user_id', 'id')->whereNull('ticket_id')->orderBy('updated_at', 'DESC')->paginate($pager);
    }

    # intro
    # - user_id ve key için kayıt varsa o intro geçilmiştir.
    public function intro(string $key)
    {
        return UserIntro::where([
            'user_id' => $this->id,
            'key' => $key
        ])->exists();
    }

    # notifications
    # - user_id ve key için kayıt varsa bildirim alınacak.
    public function notification(string $key)
    {
        return UserNotification::where([
            'user_id' => $this->id,
            'key' => $key
        ])->exists();
    }

    public function root()
    {
        return $this->root;
    }
}
