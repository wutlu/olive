<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User\PartnerPayment;

class AccountingController extends Controller
{
    public static function partnerHistory(int $id = null)
    {
    	$payments = new PartnerPayment;

    	if ($id)
    	{
    		$payments = $payments->where('user_id', $id);
    	}

    	$payments = $payments->orderBy('created_at', 'DESC')->paginate(5);

    	return view('accounting.partner_payments', compact('payments'));
    }
}
