@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Kullanıcılar'
        ],
        [
            'text' => $user->name
        ]
    ]
])

@section('wildcard')
    <div class="card wild-background mb-0">
        <div class="container">
            <span class="wildcard-title white-text d-flex flex-wrap">
        		<img alt="Avatar" src="{{ $user->avatar() }}" class="mr-1 circle align-self-center" />
                <span class="align-self-center">{{ $user->name }}</span>
            </span>
        </div>
    </div>
    @if ($user->badges->count())
	    <div class="card">
	    	<div class="container">
	    		<div class="pt-1 pb-1">
		            <div class="d-flex flex-wrap">
		                @foreach ($user->badges()->orderBy('id', 'ASC')->get() as $badge)
		                    <img
		                        alt="{{ config('system.user.badges')[$badge->badge_id]['name'] }}"
		                        src="{{ asset(config('system.user.badges')[$badge->badge_id]['image_src']) }}"
		                        data-tooltip="{{ config('system.user.badges')[$badge->badge_id]['name'] }} - {{ date('d.m.Y H:i', strtotime($badge->created_at)) }}"
		                        style="width: 32px; height: 32px;" />
		                @endforeach
		            </div>
	            </div>
	        </div>
	    </div>
    @endif
@endsection

@push('external.include.header')
    <meta property="og:title" content="{{ $user->name }}">
    <meta property="og:type" content="category" />
    <meta property="og:url" content="{{ url()->full() }}" />
    <meta property="og:image" content="{{ $user->avatar() }}" />

    <meta name="twitter:card" content="app" />
    <meta name="twitter:site" content="{{ url()->full() }}" />
    <meta name="twitter:title" content="{{ $user->name }}" />
    <meta name="twitter:image" content="{{ $user->avatar() }}" />
@endpush

@section('content')
	<div class="card">
		<ul class="collection">
			<li class="collection-item">
				<a href="{{ route('forum.group', [ __('route.forum.user'), $user->id ]) }}">
					Açtığı Konular
				</a>
				<span class="badge grey white-text">{{ $user->messages()->count() }}</span>
			</li>
		</ul>
	</div>
@endsection

@push('local.scripts')
    $(document).ready(function() {
        $('.tabs').tabs()
    })
@endpush
