@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => $title
        ]
    ]
])

@include('content._inc.histogram', [
    'charts' => [
        [
            'type' => 'video-by-comment',
            'period' => 'daily',
            'title' => 'Video Yükledi (Gün)',
            'id' => $document['_id'],
            'unique_id' => 'tab_1',
            'active' => true
        ],
        [
            'type' => 'video-by-comment',
            'period' => 'hourly',
            'title' => 'Video Yükledi (Saat)',
            'id' => $document['_id'],
            'unique_id' => 'tab_2'
        ],
        [
            'type' => 'comment-by-comment',
            'period' => 'daily',
            'title' => 'Yorum Yaptı (Gün)',
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
