<?php

namespace App\Models\Discount;

use Illuminate\Database\Eloquent\Model;

class DiscountDay extends Model
{
	protected $table = 'discount_days';
	protected $fillable = [
		'first_day',
		'last_day',
		'discount_rate',
		'discount_price'
	];
}
