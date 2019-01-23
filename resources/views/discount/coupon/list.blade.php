@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => '🐞 İndirim Kuponları'
        ]
    ],
    'dock' => true
])

@push('local.scripts')
    @if (session('status') == 'deleted')
        M.toast({ html: 'Kupon Silindi', classes: 'green darken-2' })
    @endif
@endpush

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/card-header.jpg') }}" alt="İndirim Kuponları" />
            <a href="{{ route('admin.discount.coupon') }}" class="btn-floating btn-large halfway-fab waves-effect white">
                <i class="material-icons grey-text text-darken-2">add</i>
            </a>
        </div>
        <div class="card-content">
            <span class="card-title">İndirim Kuponları</span>
            <p class="grey-text">{{ count($coupons).'/'.$coupons->total() }}</p>

            @if (!count($coupons))
                @component('components.nothing')@endcomponent
            @endif
        </div>
        @if (count($coupons))
        <div class="collection">
            @foreach ($coupons as $coupon)
            <a href="{{ route('admin.discount.coupon', $coupon->id) }}" class="collection-item d-flex waves-effect justify-content-between">
                <span class="align-self-center">
                    <p class="mb-0">{{ $coupon->rate.'%' }} indirim</p>
                    <p class="mb-0">{{ config('formal.currency').' '.$coupon->price }} indirim</p>
                </span>
                <small class="grey-text">{{ $coupon->key }}</small>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    {!! $coupons->links('vendor.pagination.materializecss') !!}
@endsection

@section('dock')
    @include('discount._menu', [ 'active' => 'coupons' ])
@endsection
