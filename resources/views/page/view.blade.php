@extends('layouts.app', [
    'term' => 'hide',
    'dock' => true
])

@section('title', $page->title)

@push('external.include.header')
    @if ($page->description)
    <!-- description -->
    <meta name="description" content="{{ $page->description }}" />
    @endif

    @if ($page->keywords)
    <!-- keywords -->
    <meta name="keywords" content="{{ $page->keywords }}" />
    @endif
@endpush

@section('content')
    <div class="navbar-fixed">
        <nav class="white">
            <a href="#" data-target="slide-out" class="sidenav-trigger show-on-medium-and-up">
                <i class="material-icons grey-text">menu</i>
            </a>
            <a href="{{ route('home') }}" class="brand-logo center">
                <img alt="{{ config('app.name') }}" src="{{ asset('img/olive-logo.svg') }}" />
            </a>
        </nav>
    </div>
    <header id="main">
        <div class="parallax-container">
            <div class="parallax indigo lighten-5">
                <img src="{{ asset('img/bg-2.svg') }}" alt="bg-1" />
            </div>

            <div class="container">
                <div class="card card-unstyled">
                    <div class="card-content">
                        <span class="card-title">{{ $page->title }}</span>
                        {!! $page->body !!}
                    </div>
                </div>
            </div>
        </div>
    </header>

    <ul id="slide-out" class="sidenav">
        <li class="p-1">
            <img alt="{{ config('app.name') }}" src="{{ asset('img/olive-logo.svg') }}" style="width: 128px;" />
        </li>
        @forelse ($pages as $page)
            <li>
                <a href="{{ route('page.view', $page->slug) }}" class="waves-effect">{{ $page->title }}</a>
            </li>
        @endforeach
    </ul>
@endsection

@push('local.scripts')
    $(document).ready(function() {
        $('.parallax').parallax()
        $('.sidenav').sidenav()
        $('.tabs').tabs()
    })
@endpush
