@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'İndirim Günü Listesi'
        ]
    ],
    'dock' => true
])

@section('content')
	<div class="card">
	    <div class="card-image">
	        <img src="{{ asset('img/md-s/19.jpg') }}" alt="Destek Taleplerim" />
	        <a href="{{ route('admin.discount.day') }}" class="btn-floating btn-large halfway-fab waves-effect white">
	            <i class="material-icons black-text">add</i>
	        </a>
	    </div>
		<div class="card-content yellow rounded-0">
			Belirleyeceğiniz günlerde sisteme kaydolan kullanıcılar için sistem, indirim kuponları üretir.
		</div>
	    <div class="card-content">
	        <span class="card-title">İndirim Günü Listesi</span>
	        <p class="grey-text">{{ count($days).'/'.$days->total() }}</p>

	        @if (!count($days))
	            <div class="not-found">
	                <i class="material-icons">cloud</i>
	                <i class="material-icons">cloud</i>
	                <i class="material-icons">wb_sunny</i>
	                <p>İndirim Günü Yok</p>
	            </div>
	        @endif
	    </div>
	    @if (count($days))
	    <div class="collection">
	        @foreach ($days as $day)
	        <a href="{{ route('admin.discount.day', $day->id) }}" class="collection-item d-flex waves-effect">
	            <span class="align-self-center">
	                <p>{{ $day->discount_rate.'%' }} indirim</p>
	                <p>{{ '₺ '.$day->discount_price }} indirim</p>
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
