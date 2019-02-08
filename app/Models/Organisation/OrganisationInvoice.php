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
		'plan_id',
		'billing_information_id'
	];

	public $incrementing = false;
	protected $primaryKey = 'invoice_id';

    protected $dates = [ 'paid_at' ];

    # fatura bilgileri
    public function info()
    {
        return $this->hasOne('App\Models\BillingInformation', 'id', 'billing_information_id');
    }

    # indirim kuponu
    public function discountCoupon()
    {
        return $this->hasOne('App\Models\Discount\DiscountCoupon', 'invoice_id', 'invoice_id');
    }

    # plan
    public function plan()
    {
        return json_decode(json_encode(config('plans')[$this->plan_id]));
    }

    # ücret bilgileri
    public function fee()
    {
    	$arr = [
    		'discount' => null,
    		'total_price' => $this->total_price
    	];

    	if ($this->discountCoupon)
    	{
	    	$arr['discount']['rate'] = $this->discountCoupon->rate_year ? ($this->discountCoupon->rate_year + $this->discountCoupon->rate) : $this->discountCoupon->rate;
	    	$arr['discount']['amount'] = $this->total_price / 100 * $arr['discount']['rate'];

    		$arr['total_price'] = $arr['total_price'] - $arr['discount']['amount'];

    		if ($this->discountCoupon->price)
    		{
	    		$arr['discount']['price'] = $this->discountCoupon->price >= $arr['total_price'] ? $arr['total_price'] : $this->discountCoupon->price;

	    		$arr['total_price'] = $arr['total_price'] - $arr['discount']['price'];
	    	}
	    }

    	$arr['amount_of_tax'] = $arr['total_price'] / 100 * $this->tax;

    	$arr['total_price'] = $arr['total_price'] + $arr['amount_of_tax'];

    	return (object) $arr;
    }

    # organizasyon
    public function organisation()
    {
        return $this->hasOne('App\Models\Organisation\Organisation', 'id', 'organisation_id');
    }
}
