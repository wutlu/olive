@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'İndirim Günleri'
        ]
    ],
    'dock' => true
])

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/card-header.jpg') }}" alt="İndirim Günleri" />
            <a href="{{ route('admin.discount.day') }}" class="btn-floating btn-large halfway-fab waves-effect white black-text">
                <i class="material-icons">add</i>
            </a>
        </div>
        <div class="card-content grey lighten-4 grey-text">
            Belirleyeceğiniz günlerde sisteme kaydolan kullanıcılar için sistem, indirim kuponları üretir.
        </div>
        <div class="card-content">
            <span class="card-title">İndirim Günleri</span>
            <p class="grey-text">{{ count($days).'/'.$days->total() }}</p>

            @if (!count($days))
                @component('components.nothing')@endcomponent
            @endif
        </div>
        @if (count($days))
        <div class="collection">
            @foreach ($days as $day)
            <a href="{{ route('admin.discount.day', $day->id) }}" class="collection-item d-flex waves-effect">
                <span class="align-self-center">
                    <p>{{ $day->discount_rate.'%' }} indirim</p>
                    <p>{{ config('formal.currency').' '.$day->discount_price }} indirim</p>
                </span>
                <small class="badge ml-auto">{{ date('d.m.Y', strtotime($day->first_day)).' / '.date('d.m.Y', strtotime($day->last_day)) }}</small>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    {!! $days->links('vendor.pagination.materializecss') !!}
@endsection

@section('dock')
    @include('discount._menu', [ 'active' => 'days' ])
@endsection
