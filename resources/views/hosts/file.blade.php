@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => '🐞 Hosts Dosyası'
        ]
    ]
])

@push('local.styles')
    ul#ip-list > li {
        padding: 0 0 .4rem 0;
    }
@endpush

@section('content')
    <div class="card with-bg">
        <div class="card-tabs">
            <ul class="tabs tabs-fixed-width">
                <li class="tab">
                    <a href="#hosts-file" class="active">Host Dosyası</a>
                </li>
                <li class="tab">
                    <a href="#ip-list">IP Listesi</a>
                </li>
            </ul>
        </div>
        <div class="card-content">
            <pre id="hosts-file">{{ $text }}</pre>
            <ul id="ip-list" style="display: none;">
                @forelse ($ip_list as $row)
                    <li class="d-flex flex-column">
                        <span>{{ str_replace([ 'https://', 'http://', 'www.' ], '', $row->site) }}</span>
                        <span class="grey-text">{{ $row->ip_address }}</span>
                    </li>
                @empty
                    <li>IP listesi boş.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection

@push('local.scripts')
    $('.tabs').tabs()
@endpush
