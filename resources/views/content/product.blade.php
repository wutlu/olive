@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => $title
        ]
    ]
])

@include('content._inc.histogram', [
    'index' => $es->index,
    'type' => $es->type,
    'id' => $document['_source']['id'],
])

@section('content')
    <div class="card">
        <div class="card-content">
            <span class="card-title">{{ $document['_source']['title'] }}</span>
            <ul class="item-group mb-0">
                <li class="item">test</li>
                <li class="item">test</li>
                <li class="item">test</li>
                <li class="item">test</li>
            </ul>
        </div>
    </div>
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/chart.js?v='.config('system.version')) }}"></script>
@endpush
