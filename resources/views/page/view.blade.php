@extends('layouts.app', [
    'term' => 'hide',
    'email' => 'hide',
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
                <img alt="{{ config('app.name') }}" src="{{ asset('img/olive_logo.svg') }}" />
            </a>
        </nav>
    </div>

    <div class="container pt-2 pb-2">
        <div class="card card-unstyled">
            <div class="card-content">
                <div class="markdown"> 
                    {!! $page->markdown() !!}
                </div>
            </div>
        </div>
    </div>

    <ul id="slide-out" class="sidenav pt-1 pb-1">
        @forelse ($pages as $page)
            <li>
                <a href="{{ route('page.view', $page->slug) }}" class="waves-effect">{{ $page->title }}</a>
            </li>
        @endforeach
    </ul>
@endsection

@push('local.scripts')
    $(document).ready(function() {
        $('.sidenav').sidenav()
        $('.tabs').tabs()
    })
@endpush
