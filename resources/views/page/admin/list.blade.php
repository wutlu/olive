@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Sayfalar'
        ]
    ]
])

@push('local.scripts')
    @if (session('status') == 'deleted')
        M.toast({ html: 'Sayfa Silindi', classes: 'green darken-2' })
    @endif
@endpush

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/card-header.jpg') }}" alt="Sayfalar" />
            <a href="{{ route('admin.page') }}" class="btn-floating btn-large halfway-fab waves-effect white">
                <i class="material-icons grey-text text-darken-2">add</i>
            </a>
        </div>
        <div class="card-content">
            <span class="card-title mb-0">Sayfalar</span>
            <p class="grey-text">{{ count($pages).'/'.$pages->total() }}</p>

            @if (!count($pages))
                @component('components.nothing')@endcomponent
            @endif
        </div>
        @if (count($pages))
        <div class="collection">
            @foreach ($pages as $page)
            <a href="{{ route('admin.page', $page->id) }}" class="collection-item d-flex waves-effect">
                <span>
                    <p class="mb-0">{{ $page->title }}</p>
                    <p class="mb-0 grey-text">{{ url($page->slug) }}</p>
                </span>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    {!! $pages->links('vendor.pagination.materializecss') !!}
@endsection
