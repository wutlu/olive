<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Organisation extends Model
{
    use SoftDeletes;

    protected $table = 'organisations';
    protected $fillable = [
		'name',
		'capacity',
		'start_date',
		'end_date',
		'user_id',
        'status'
    ];

    protected $dates = [ 'deleted_at' ];

    # users
    public function users()
    {
        return $this->hasMany('App\User', 'organisation_id', 'id');
    }

    # author
    public function author()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    # faturalar
    public function invoices(int $take = 1)
    {
        return $this->hasMany('App\OrganisationInvoice', 'organisation_id', 'id')->orderBy('created_at', 'DESC')->limit($take)->get();
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
