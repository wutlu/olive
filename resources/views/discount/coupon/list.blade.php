@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'İndirim Kuponları'
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
                <i class="material-icons black-text">add</i>
            </a>
        </div>
        <div class="card-content">
            <span class="card-title">İndirim Kuponları</span>
            <p class="grey-text">{{ count($coupons).'/'.$coupons->total() }}</p>

            @if (!count($coupons))
                <div class="not-found">
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">wb_sunny</i>
                    <p>Kupon Yok</p>
                </div>
            @endif
        </div>
        @if (count($coupons))
        <div class="collection">
            @foreach ($coupons as $coupon)
            <a href="{{ route('admin.discount.coupon', $coupon->id) }}" class="collection-item d-flex waves-effect">
                <span class="align-self-center">
                    <p>{{ $coupon->rate.'%' }} indirim</p>
                    <p>{{ '₺ '.$coupon->price }} indirim</p>
                </span>
                <small class="badge ml-auto">{{ $coupon->key }}</small>
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
