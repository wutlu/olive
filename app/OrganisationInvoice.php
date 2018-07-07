<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrganisationInvoice extends Model
{
	protected $table = 'organisation_invoices';
	protected $fillable = [
		'organisation_id',
		'invoice_id',
		'user_id',
		'name',
		'lastname',
		'address',
		'json',
		'notes',
		'unit_price',
		'total_price',
		'discount',
		'tax',
		'paid'
	];
}
