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

@section('action-bar:half')
    <a href="{{ route('admin.discount.day') }}" class="btn-floating btn-large halfway-fab waves-effect white">
        <i class="material-icons grey-text text-darken-2">add</i>
    </a>
@endsection

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">İndirim Günleri</span>
            <p class="grey-text text-darken-2">{{ count($days).'/'.$days->total() }}</p>
            <p class="grey-text text-darken-2">Belirleyeceğiniz günlerde sisteme kaydolan kullanıcılar için sistem, indirim kuponları üretir.</p>

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
