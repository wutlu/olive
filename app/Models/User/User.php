<?php

namespace App\Models\User;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Utilities\UserActivityUtility;
use App\Notifications\MessageNotification;

use System;

use App\Models\Session;

use Carbon\Carbon;

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
        'term_version'
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
        'root',
        'moderator'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'root' => 'boolean',
        'moderator' => 'boolean'
    ];

    # avatar
    public function avatar()
    {
        return asset($this->avatar ? $this->avatar : 'img/icons/people.svg');
    }

    # online
    public function online()
    {
        $session = Session::where('user_id', $this->id)->first();

        $date = Carbon::now()->subMinutes(1)->timestamp;

        return @$session ? ($session->last_activity >= $date) : false;
    }

    # rozet ekle
    public function addBadge(int $id)
    {
        $badge = @config('system.user.badges')[$id];

        if ($badge)
        {
            $greeting = '🌠🌟✨ '.$badge['name'].' ✨🌟🌠';
            $message = 'Tebrikler!'.PHP_EOL.'"'.$badge['name'].'" rozetini kazandınız.';

            UserActivityUtility::push(
                $greeting,
                [
                    'key'       => implode('-', [ 'badge', $this->id ]),
                    'icon'      => 'star',
                    'markdown'  => $message,
                    'user_id'   => $this->id,
                    'push'      => true
                ]
            );

            if ($this->notification('badge'))
            {
                $this->notify((new MessageNotification('Olive: 🌠🌟✨ Yeni Rozet ✨🌟🌠', $greeting, $message))->onQueue('email'));
            }

            Badge::firstOrCreate([
                'user_id' => $this->id,
                'badge_id' => $id
            ]);
        }
        else
        {
            System::log('['.$id.'] rozeti bulunamadı.', 'App\Models\User\User::handle()', 4);
        }
    }

    # rozet kontrolü
    public function badge(int $id)
    {
        $badge = Badge::where([
            'user_id' => $this->id,
            'badge_id' => $id
        ])->exists();

        if ($badge)
        {
            return config('system.user.badges')[$id];;
        }
        else
        {
            return false;
        }
    }

    # rozetler
    public function badges()
    {
        return $this->hasMany('App\Models\User\Badge', 'user_id', 'id');
    }

    # oturum
    public function session()
    {
        return $this->hasOne('App\Models\Session', 'id', 'session_id');
    }

    # forum mesajları
    public function messages()
    {
        return $this->hasMany('App\Models\Forum\Message', 'user_id', 'id');
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
        return $this->hasMany('App\Models\Organisation\OrganisationInvoice', 'user_id', 'id')->orderBy('created_at', 'DESC');
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
        return $this->verified ? UserNotification::where([
            'user_id' => $this->id,
            'key' => $key
        ])->exists() : false;
    }

    # notifications
    public function notifications()
    {
        return $this->hasMany('App\Models\User\UserNotification', 'user_id', 'id');
    }

    public function root()
    {
        return $this->root;
    }

    public function moderator()
    {
        return $this->moderator;
    }

    public function getEmailAttribute($value)
    {
        return $value ? $value : 'anonymous@veri.zone';
    }
}
