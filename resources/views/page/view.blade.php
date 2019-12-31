@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'term' => 'hide',
    'email' => 'hide',
    'title' => [
        'text' => $page->title
    ],
    'description' => $page->description,
    'keywords' => $page->keywords
])

@push('local.styles')
    h1, h2, h3, h4, h5, h6 {
        color: #666;
    }

    p {
        color: #666;
    }
@endpush

@section('wildcard')
    <div class="card wild-background">
        <div class="container">
            <span class="wildcard-title">{{ $page->title }}</span>
        </div>
    </div>
@endsection

@section('content')
    <div class="card card-unstyled">
        <div class="card-content">
            <div class="markdown"> 
                {!! $page->markdown() !!}
            </div>
        </div>
    </div>
@endsection
