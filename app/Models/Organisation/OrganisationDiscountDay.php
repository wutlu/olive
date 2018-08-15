<?php

namespace App\Models\Organisation;

use Illuminate\Database\Eloquent\Model;

class OrganisationDiscountDay extends Model
{
	protected $table = 'organisation_discount_days';
	protected $fillable = [
		'first_day',
		'last_day',
		'discount_rate'
	];
}
