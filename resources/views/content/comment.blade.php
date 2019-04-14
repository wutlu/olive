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
    ]
])

@include('content._inc.histogram', [
    'charts' => [
        [
            'type' => 'video-by-comment',
            'period' => 'daily',
            'title' => 'Günlük Video Yükleme',
            'id' => $document['_id'],
            'unique_id' => 'tab_1',
            'active' => true
        ],
        [
            'type' => 'video-by-comment',
            'period' => 'hourly',
            'title' => 'Saatlik Video Yükleme',
            'id' => $document['_id'],
            'unique_id' => 'tab_2'
        ],
        [
            'type' => 'comment-by-comment',
            'period' => 'daily',
            'title' => 'Günlük Yorum',
            'id' => $document['_id'],
            'unique_id' => 'tab_3'
        ],
        [
            'type' => 'comment-by-comment',
            'period' => 'hourly',
            'title' => 'Yorum Yaptı (Saat)',
            'id' => $document['_id'],
            'unique_id' => 'tab_4'
        ],
    ]
])

@push('local.styles')
    [data-name=title] {
        font-size: 18px;
    }
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/chart.min.js?v='.config('system.version')) }}"></script>
@endpush
