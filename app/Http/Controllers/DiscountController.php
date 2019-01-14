<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\Discount\Coupon\CreateRequest;
use App\Http\Requests\Discount\Coupon\UpdateRequest;
use App\Http\Requests\IdRequest;
use App\Http\Requests\Discount\Day\CreateRequest as DayCreateRequest;
use App\Http\Requests\Discount\Day\UpdateRequest as DayUpdateRequest;

use App\Models\Discount\DiscountCoupon as Coupon;
use App\Models\Discount\DiscountDay as Day;

class DiscountController extends Controller
{
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * İndirim Kuponu Listesi
     *
     * @return view
     */
    public static function adminCouponListView(int $pager = 10)
    {
        $coupons = Coupon::whereNull('invoice_id')->paginate($pager);

        return view('discount.coupon.list', compact('coupons'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * İndirim Kuponu Sayfası
     *
     * @return view
     */
    public static function adminCouponView(int $id = 0)
    {
        if ($id)
        {
            $coupon = Coupon::where('id', $id)->whereNull('invoice_id')->firstOrFail();
            $coupon->count = Coupon::where('key', $coupon->key)->whereNull('invoice_id')->count();
        }
        else
        {
            $coupon = [];
        }

        return view('discount.coupon.view', compact('coupon'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * İndirim Kuponu Güncelle
     *
     * @return array
     */
    public static function adminCouponUpdate(UpdateRequest $request)
    {
        $coupon = Coupon::where('id', $request->id)->whereNull('invoice_id')->firstOrFail();
        $count  = Coupon::where('key', $coupon->key)->whereNull('invoice_id')->count();

        Coupon::where('key', $coupon->key)->whereNull('invoice_id')->update(
            [
                'key' => $request->key,
                'rate' => $request->rate,
                'price' => $request->price
            ]
        );

        if ($request->count > $count)
        {
            for ($i = 1; $i <= ($request->count - $count); $i++)
            {
                $coupon = new Coupon;
                $coupon->key = $request->key;
                $coupon->rate = $request->rate;
                $coupon->price = $request->price;
                $coupon->save();
            }
        }
        else if ($request->count < $count)
        {
            $deleted = Coupon::where('key', $coupon->key)
                             ->whereNull('invoice_id')
                             ->where('id', '<>', $request->id)
                             ->take(($count - $request->count))
                             ->get();

            if (count($deleted))
            {
                foreach ($deleted as $delete)
                {
                    $delete->delete();
                }
            }
        }

        return [
            'status' => 'ok',
            'data' => [
                'status' => 'updated'
            ]
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * İndirim Kuponu Oluştur
     *
     * @return array
     */
    public static function adminCouponCreate(CreateRequest $request)
    {
        for ($i = 1; $i <= $request->count; $i++)
        {
            $coupon = new Coupon;
            $coupon->key = $request->key;
            $coupon->rate = $request->rate;
            $coupon->price = $request->price;
            $coupon->save();
        }

        session()->flash('status', 'created');

        return [
            'status' => 'ok',
            'data' => [
                'status' => 'created',
                'route' => route('admin.discount.coupon', $coupon->id)
            ]
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * İndirim Kuponu Sil
     *
     * @return array
     */
    public static function adminCouponDelete(IdRequest $request)
    {
        $coupon = Coupon::where('id', $request->id)->firstOrFail();

        Coupon::where('key', $coupon->key)->delete();

        session()->flash('status', 'deleted');

        return [
            'status' => 'ok'
        ];
    }

    ### ### ###

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * İndirim Günü Listesi
     *
     * @return view
     */
    public static function adminDayListView(int $pager = 10)
    {
        $days = Day::paginate($pager);

        return view('discount.day.list', compact('days'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * İndirim Günü Sayfası
     *
     * @return view
     */
    public static function adminDayView(int $id = 0)
    {
        $day = $id ? Day::where('id', $id)->firstOrFail() : [];

        return view('discount.day.view', compact('day'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * İndirim Günü Oluştur
     *
     * @return array
     */
    public static function adminDayCreate(DayCreateRequest $request)
    {
        $day = new Day;
        $day->fill($request->all());
        $day->save();

        session()->flash('status', 'created');

        return [
            'status' => 'ok',
            'data' => [
                'status' => 'created',
                'route' => route('admin.discount.day', $day->id)
            ]
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * İndirim Günü Güncelle
     *
     * @return array
     */
    public static function adminDayUpdate(DayUpdateRequest $request)
    {
        $day = Day::where('id', $request->id)->firstOrFail();
        $day->fill($request->all());
        $day->save();

        return [
            'status' => 'ok',
            'data' => [
                'status' => 'updated'
            ]
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * İndirim Günü Sil
     *
     * @return array
     */
    public static function adminDayDelete(IdRequest $request)
    {
        $day = Day::where('id', $request->id)->delete();

        session()->flash('status', 'deleted');

        return [
            'status' => 'ok'
        ];
    }
}
