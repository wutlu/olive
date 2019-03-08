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
            'type' => 'video-comments',
            'period' => 'daily',
            'title' => 'Videoya Yorum Yapıldı (Gün)',
            'id' => $document['_id'],
            'unique_id' => 'tab_1',
            'active' => true
        ],
        [
            'type' => 'video-comments',
            'period' => 'hourly',
            'title' => 'Videoya Yorum Yapıldı (Saat)',
            'id' => $document['_id'],
            'unique_id' => 'tab_2'
        ],
        [
            'type' => 'video-by-video',
            'period' => 'daily',
            'title' => 'Video Yükledi (Gün)',
            'id' => $document['_id'],
            'unique_id' => 'tab_3',
        ],
        [
            'type' => 'video-by-video',
            'period' => 'hourly',
            'title' => 'Video Yükledi (Saat)',
            'id' => $document['_id'],
            'unique_id' => 'tab_4'
        ],
        [
            'type' => 'comment-by-video',
            'period' => 'daily',
            'title' => 'Yorum Yaptı (Gün)',
            'id' => $document['_id'],
            'unique_id' => 'tab_5'
        ],
        [
            'type' => 'comment-by-video',
            'period' => 'hourly',
            'title' => 'Yorum Yaptı (Saat)',
            'id' => $document['_id'],
            'unique_id' => 'tab_6'
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
