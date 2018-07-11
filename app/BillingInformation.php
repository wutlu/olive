<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillingInformation extends Model
{
    protected $table = 'billing_informations';
    protected $fillable = [
		'type',
		'name',
		'person_name',
		'person_lastname',
		'person_tckn',
		'merchant_name',
		'tax_number',
		'tax_office',
		'country_id',
		'state_id',
		'city',
		'address',
		'postal_code',
		'protected'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'protected',
        'user_id'
    ];

    public function state()
    {
        return $this->hasOne('App\Models\Geo\States', 'id', 'state_id');
    }

    public function country()
    {
        return $this->hasOne('App\Models\Geo\Countries', 'id', 'country_id');
    }
}
