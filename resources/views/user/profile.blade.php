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
        		<img alt="Avatar" src="{{ $user->avatar() }}" class="mr-1 circle align-self-center" style="width: 96px; height: 96px;" />
                <span class="align-self-center">{{ $user->name }}</span>
            </span>
        </div>
    </div>
    @if ($user->badges->count())
	    <div class="card">
	    	<div class="container">
	    		<div class="pt-1 pb-1">
		            <div class="d-flex flex-wrap">
		                @foreach (config('system.user.badges') as $id => $badge)
                            @php
                            $have = $user->badge($id);
                            @endphp

                            <a href="#" data-trigger="badge" data-text="{{ $badge['description'] }}">
    		                    <img
    		                        alt="{{ $badge['name'] }}"
    		                        src="{{ asset($badge['image_src']) }}"
    		                        data-tooltip="{{ $badge['name'] }}"
                                    class="rosette {{ $have ? 'active' : '' }}" />
                            </a>
		                @endforeach
		            </div>
	            </div>
	        </div>
	    </div>
    @endif
@endsection

@push('local.scripts')
    $(document).on('click', '[data-trigger=badge]', function() {
        modal({
            'id': 'badge',
            'body': $(this).data('text'),
            'size': 'modal-small',
            'title': 'Nasıl Alırım?',
            'options': {},
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect btn-flat cyan-text',
                    'html': buttons.ok
                })
            ]
        });
    })
@endpush

@push('local.styles')
    .rosette {
        width: 64px;
        height: 64px;
        padding: .2rem;

                filter: grayscale(100%);
        -webkit-filter: grayscale(100%);
    }
    .rosette.active {
                filter: grayscale(0);
        -webkit-filter: grayscale(0);
    }
@endpush

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
        @if ($user->about)
            <div class="card-content grey lighten-4">
                <span class="card-title">Hakkında</span>
                <div class="markdown">{!! Term::markdown($user->about) !!}</div>
            </div>
        @endif
		<ul class="collection">
			<li class="collection-item">
				<a href="{{ route('forum.group', [ __('route.forum.user'), $user->id ]) }}">Açtığı Konular</a>
				<span class="badge grey white-text">{{ $user->messages()->whereNull('message_id')->count() }}</span>
			</li>
		</ul>
	</div>
@endsection

@push('local.scripts')
    $(document).ready(function() {
        $('.tabs').tabs()
    })
@endpush
