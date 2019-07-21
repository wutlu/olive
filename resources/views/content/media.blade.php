@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => $title
        ]
    ],
    'pin_group' => true,
    'delete' => [
        'id' => $document['_id'],
        'type' => $document['_type'],
        'index' => $document['_index']
    ],
    'footer_hide' => true
])

@include('content._inc.histogram', [
    'charts' => [
        [
            'type' => 'media',
            'period' => 'daily',
            'title' => 'Günlük Medya Paylaşımı',
            'id' => $document['_id'],
            'unique_id' => 'tab_1',
            'es_index_key' => date('Y.m', strtotime($document['_source']['created_at'])),
            'active' => true
        ],
        [
            'type' => 'media',
            'period' => 'hourly',
            'title' => 'Saatlik Medya Paylaşımı',
            'id' => $document['_id'],
            'unique_id' => 'tab_2',
            'es_index_key' => date('Y.m', strtotime($document['_source']['created_at']))
        ]
    ]
])

@push('wildcard-top')
    <div class="card">
        <div class="card-content d-flex justify-content-between">
            <span class="align-self-center">
                <span class="card-title">{{ $data['user']['name'] }} <a href="https://www.instagram.com/{{ $data['user']['screen_name'] }}/" target="_blank" class="green-text">{{ '@'.$data['user']['screen_name'] }}</a></span>
                <a href="https://www.instagram.com/p/{{ $document['_source']['shortcode'] }}/" target="_blank" class="grey-text">https://www.instagram.com/p/{{ $document['_source']['shortcode'] }}/</a>
            </span>
            <img alt="instagram" src="{{ asset('img/logos/instagram.svg') }}" class="align-self-center" style="width: 64px;" />
        </div>
    </div>
@endpush

@section('content')
    <div class="card mb-1">
        @include('content._inc.pin_bar', [
            'document' => $document
        ])
    </div>
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/chart.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.cloud.min.js?v='.config('system.version')) }}"></script>
@endpush
