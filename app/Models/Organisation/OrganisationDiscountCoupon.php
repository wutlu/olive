<?php

namespace App\Models\Organisation;

use Illuminate\Database\Eloquent\Model;

class OrganisationDiscountCoupon extends Model
{
    protected $table = 'organisation_discount_coupons';
    protected $fillable = [
		'key',
		'rate',
		'rate_year',
		'price',
		'invoice_id'
    ];
}
