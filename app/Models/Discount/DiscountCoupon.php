<?php

namespace App\Models\Discount;

use Illuminate\Database\Eloquent\Model;

class DiscountCoupon extends Model
{
    protected $table = 'discount_coupons';
    protected $fillable = [
		'key',
		'rate',
		'rate_year',
		'price',
		'invoice_id'
    ];
}
