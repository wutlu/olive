@extends('layouts.app')

@push('external.include.header')
	@if ($page->title)
    <!-- description -->
    @section('title', $page->title)
	@endif

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
    <header id="main">
        <div class="parallax-container">
            <div class="parallax indigo lighten-5">
                <img src="{{ asset('img/bg-2.svg') }}" alt="bg-1" />
            </div>

            <div class="container">
                <img alt="Logo" src="{{ asset('img/olive-logo.svg') }}" style="max-width: 200px;" />
                <div class="card card-unstyled">
                    <div class="card-content">
                    	<span class="card-title">{{ $page->title }}</span>
                        {!! $page->body !!}
                    </div>
                </div>
            </div>
        </div>
    </header>
@endsection

@push('local.scripts')
	$(document).ready(function() {
        $('.parallax').parallax();
    });
@endpush
