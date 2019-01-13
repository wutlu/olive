<?php

namespace App\Models\Organisation;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Organisation extends Model
{
    protected $table = 'organisations';
    protected $fillable = [
		'name'
    ];

    # users
    public function users()
    {
        return $this->hasMany('App\Models\User\User', 'organisation_id', 'id');
    }

    # author
    public function author()
    {
        return $this->hasOne('App\Models\User\User', 'id', 'user_id');
    }

    # twitter account
    public function twitterAccount()
    {
        return $this->hasOne('App\Models\Twitter\Account', 'id', 'twitter_account_id');
    }

    # son fature
    public function lastInvoice()
    {
        return $this->hasOne('App\Models\Organisation\OrganisationInvoice', 'organisation_id', 'id')->orderBy('created_at', 'DESC');
    }

    # faturalar
    public function invoices()
    {
        return $this->hasMany('App\Models\Organisation\OrganisationInvoice', 'organisation_id', 'id')->orderBy('created_at', 'DESC');
    }

    /**
     * twitter area
     */

    # takip edilecek twitter kelimeleri
    public function streamingKeywords()
    {
        return $this->hasMany('App\Models\Twitter\StreamingKeywords', 'organisation_id', 'id');
    }

    # takip edilecek twitter kullanıcılar
    public function streamingUsers()
    {
        return $this->hasMany('App\Models\Twitter\StreamingUsers', 'organisation_id', 'id');
    }

    /**
     * youtube area
     */

    # takip edilecek youtube kelimeleri
    public function youtubeFollowingKeywords()
    {
        return $this->hasMany('App\Models\YouTube\FollowingKeywords', 'organisation_id', 'id');
    }

    # takip edilecek youtube kullanıcıları
    public function youtubeFollowingChannels()
    {
        return $this->hasMany('App\Models\YouTube\FollowingChannels', 'organisation_id', 'id');
    }

    # takip edilecek youtube videoları
    public function youtubeFollowingVideos()
    {
        return $this->hasMany('App\Models\YouTube\FollowingVideos', 'organisation_id', 'id');
    }

    # kalan gün
    public function days(bool $all = false)
    {
        if ($all)
        {
            // toplam gün
            return Carbon::parse($this->start_date)->diffInDays($this->end_date);
        }
        else
        {
            // kalan gün
            return Carbon::parse($this->end_date)->diffInDays(Carbon::now());
        }
    }

    # gerçek zamanlı kelime grupları
    public function realTimeKeywordGroups()
    {
        return $this->hasMany('App\Models\RealTime\KeywordGroup', 'organisation_id', 'id');
    }

    # gerçek zamanlı kelime grupları
    public function pinGroups()
    {
        return $this->hasMany('App\Models\Pin\Group', 'organisation_id', 'id');
    }
}
