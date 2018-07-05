<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrganisationDiscountCoupon extends Model
{
    protected $table = 'organisation_discount_coupons';

    protected $fillable = [
		'key',
		'rate',
		'organisation_id'
    ];
}
