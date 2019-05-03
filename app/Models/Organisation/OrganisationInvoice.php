<?php

namespace App\Models\Organisation;

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
		'tax',
		'paid_at',
		'serial',
		'no',
		'billing_information_id',
        'plan',
        'discount_rate',
	];

	public $incrementing = false;
	protected $primaryKey = 'invoice_id';

    protected $dates = [ 'paid_at' ];

    # fatura bilgileri
    public function info()
    {
        return $this->hasOne('App\Models\BillingInformation', 'id', 'billing_information_id');
    }

    # ücret bilgileri
    public function fee()
    {
        $total_price = $this->total_price;
        $discount = ($total_price / 100) * $this->discount_rate;
        $discounted_price = $total_price - $discount;
        $total_tax = ($discounted_price / 100) * $this->tax;
        $amount = number_format((float) $total_tax + $discounted_price, 2, '.', '');

        return (object) [
            'unit' => $this->unit_price,
            'total' => $this->total_price,
            'tax' => number_format((float) $total_tax, 2, '.', ''),
            'discount' => number_format((float) $discount, 2, '.', ''),
            'discounted' => number_format((float) $discounted_price, 2, '.', ''),
            'amount' => $amount,
            'amount_int' => intval(str_replace([ ',', '.' ], '', $amount)),
        ];
    }

    # organizasyon
    public function organisation()
    {
        return $this->hasOne('App\Models\Organisation\Organisation', 'id', 'organisation_id');
    }
}
