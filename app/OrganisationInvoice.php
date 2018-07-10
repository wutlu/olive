<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrganisationInvoice extends Model
{
	protected $table = 'organisation_invoices';
	protected $fillable = [
		'invoice_id',
		'organisation_id',
		'user_id',
		'unit_price',
		'month',
		'total_price',
		'amount_of_tax',
		'discount',
		'customer',
		'pay_notice',
		'pay_confirmed',
		'plan',
	];
	public $incrementing = false;
	protected $primaryKey = 'invoice_id';
}
