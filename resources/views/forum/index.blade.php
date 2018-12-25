@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'dock' => true
])

@section('nav')
test
@endsection

@section('content')
asf
@endsection

@section('dock')
    <div class="card">
        <div class="card-content teal accent-3">
            <span class="card-title white-text mb-0">Kategoriler</span>
        </div>
        <div class="collection collection-bordered">
            @forelse($categories as $category)
                <a href="{{ route('forum.category', $category->slug) }}" class="collection-item waves-effect">
                    <span class="d-block">{{ $category->name }}</span>
                    <span class="grey-text d-block">{{ $category->description }}</span>
                </a>
            @empty
                <div class="collection-item d-block">
                    @component('components.nothing')
                        @slot('size', 'small')
                    @endcomponent
                </div>
            @endforelse
        </div>
    </div>
@endsection
