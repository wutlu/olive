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
    <div class="card">
        <div class="container">
            <div class="pt-1 pb-1">
                <div class="d-flex flex-wrap justify-content-center">
                    @foreach (config('system.user.badges') as $id => $badge)
                        @php
                        $have = $user->badge($id);
                        @endphp

                        <a href="#" class="waves-effect rosette {{ $have ? 'active' : '' }}" data-trigger="badge" data-text="{{ $badge['description'] }}">
                            <img
                                alt="{{ $badge['name'] }}"
                                src="{{ asset($badge['image_src']) }}"
                                data-tooltip="{{ $badge['name'] }}"
                                class="" />
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('local.scripts')
    $(document).on('click', '[data-trigger=badge]', function() {
        modal({
            'id': 'badge',
            'body': $(this).data('text'),
            'size': 'modal-small',
            'title': 'Nasıl Kazanılır?',
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

        padding: 1rem;
        border-radius: 50%;
    }

    .rosette > img {
        width: 100%;
        height: 100%;
    }

    .rosette + .rosette {
        margin: 0 0 0 1rem;
    }

    .rosette.active {
                filter: grayscale(0);
        -webkit-filter: grayscale(0);

        background-color: #f0f0f0;

                box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.14), 0 3px 1px -2px rgba(0, 0, 0, 0.12), 0 1px 5px 0 rgba(0, 0, 0, 0.2);
        -webkit-box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.14), 0 3px 1px -2px rgba(0, 0, 0, 0.12), 0 1px 5px 0 rgba(0, 0, 0, 0.2);
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
