@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => implode(' ', [ config('app.name'), 'Forumları' ]),
            'link' => route('forum.index')
        ],
        [
            'text' => $category->name,
            'link' => route('forum.category', $category->slug)
        ],
        [
            'text' => $thread->subject
        ]
    ]
])

@section('wildcard')
    <div class="card grey darken-4">
        @guest
            <div class="card-image">
                <a href="{{ route('user.login') }}" class="btn-floating btn-large halfway-fab waves-effect teal" data-tooltip="Giriş Yap" data-position="left">
                    <i class="material-icons">person</i>
                </a>
            </div>
        @endauth
        <div class="container">
            <span class="wildcard-title white-text">{{ $thread->subject }}</span>
        </div>
    </div>
@endsection

@section('content')
    test
@endsection
