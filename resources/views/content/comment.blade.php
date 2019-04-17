@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => $title
        ]
    ],
    'dock' => true,
    'pin_group' => true,
    'delete' => [
        'id' => $document['_id'],
        'type' => $document['_type'],
        'index' => $document['_index']
    ]
])

@push('external.include.footer')
    <script src="{{ asset('js/chart.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.mark.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
@endpush

@push('local.styles')
    .marked {
        padding: .4rem;
        border-radius: .2rem;
    }
@endpush

@include('content._inc.histogram', [
    'charts' => [
        [
            'type' => 'video-by-comment',
            'period' => 'daily',
            'title' => '<i class="material-icons align-self-center mr-1">play_arrow</i> Kanal: Yükleme Grafiği',
            'id' => $document['_id'],
            'es_index_key' => $document['_index'],
            'unique_id' => 'tab_3',
            'info' => 'İlgili videoyu yükleyen kanalın yüklemelerinin günlere dağılımı.',

            'active' => true
        ],
        [
            'type' => 'video-by-comment',
            'period' => 'hourly',
            'title' => '<i class="material-icons align-self-center mr-1">play_arrow</i> Kanal: Yükleme Grafiği',
            'id' => $document['_id'],
            'es_index_key' => $document['_index'],
            'unique_id' => 'tab_4',
            'info' => 'İlgili videoyu yükleyen kanalın yüklemelerinin saatlere dağılımı.'
        ],

        [
            'type' => 'comment-by-comment',
            'period' => 'daily',
            'title' => '<i class="material-icons align-self-center mr-1">person</i> Kanal: Yorum Grafiği',
            'id' => $document['_id'],
            'es_index_key' => $document['_index'],
            'unique_id' => 'tab_5',
            'info' => 'İlgili videoyu yükleyen kanalın yaptığı yorumların günlere dağılımı.'
        ],
        [
            'type' => 'comment-by-comment',
            'period' => 'hourly',
            'title' => '<i class="material-icons align-self-center mr-1">person</i> Kanal: Yorum Grafiği',
            'id' => $document['_id'],
            'es_index_key' => $document['_index'],
            'unique_id' => 'tab_6',
            'info' => 'İlgili videoyu yükleyen kanalın yaptığı yorumların saatlere dağılımı.'
        ],
    ]
])
