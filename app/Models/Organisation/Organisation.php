<?php

namespace App\Models\Organisation;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Organisation extends Model
{
    protected $table = 'organisations';
    protected $fillable = [
		'name',
		'capacity',
		'start_date',
		'end_date',
		'user_id',
        'status'
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
}
