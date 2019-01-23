@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => '🐞 İndirim Günleri'
        ]
    ],
    'dock' => true
])

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/card-header.jpg') }}" alt="İndirim Günleri" />
            <a href="{{ route('admin.discount.day') }}" class="btn-floating btn-large halfway-fab waves-effect white">
                <i class="material-icons grey-text text-darken-2">add</i>
            </a>
        </div>
        <div class="card-content teal lighten-5 teal-text">
            <p>Belirleyeceğiniz günlerde sisteme kaydolan kullanıcılar için sistem, indirim kuponları üretir.</p>
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
            <a href="{{ route('admin.discount.day', $day->id) }}" class="collection-item d-flex waves-effect justify-content-between">
                <span class="align-self-center">
                    <p class="mb-0">{{ $day->discount_rate.'%' }} indirim</p>
                    <p class="mb-0">{{ config('formal.currency').' '.$day->discount_price }} indirim</p>
                </span>
                <small class="badge">{{ date('d.m.Y', strtotime($day->first_day)).' / '.date('d.m.Y', strtotime($day->last_day)) }}</small>
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
