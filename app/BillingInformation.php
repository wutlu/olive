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
}
