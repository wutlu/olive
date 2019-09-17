@extends('layouts.app', [
    'sidenav_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Trend Analizi'
        ],
        [
            'text' => 'Trend Arşivi',
            'link' => route('trend.archive')
        ],
        [
            'text' => $module['name'].', '.$query->group
        ]
    ],
    'footer_hide' => true,
    'dock' => true
])

@section('dock')
    @include('trends._menu', [ 'active' => 'archive_view' ])
@endsection

@section('content')
    <div class="card card-unstyled">
        <span class="card-title">{{ $module['name'].', '.$query->group }}</span>

        <ul class="collection collection-unstyled collection-hoverable"> 
            @if ($documents->status == 'ok')
                @forelse(@$documents->data['hits']['hits'] as $key => $document)
                    <li class="collection-item">
                        <div class="d-flex">
                            <span class="center-align mr-1" style="max-width: 72px;">
                                <span class="mx-auto circle white d-table center-align mb-1" style="width: 48px; line-height: 48px;">{{ $key+1 }}</span>
                                <span class="mx-auto">{{ number_format($document['_source']['hit']) }}</span>
                            </span>
                            <span class="align-self-center">
                                @isset ($document['_source']['data']['user'])
                                    <div class="d-flex mb-1">
                                        <img alt="Image" src="{{ $document['_source']['data']['user']['image'] }}" style="width: 48px; height: 48px;" class="mr-1" />
                                        <span style="line-height: 24px;">
                                            <span class="d-flex">
                                                {{ $document['_source']['data']['user']['name'] }}
                                                @isset ($document['_source']['data']['user']['verified'])
                                                    <i class="material-icons blue-text align-self-center ml-1">check</i>
                                                @endisset
                                            </span>
                                            <a href="https://twitter.com/intent/user?user_id={{ $document['_source']['data']['user']['id'] }}" class="d-table grey-text" target="_blank">{{ '@'.$document['_source']['data']['user']['screen_name'] }}</a>
                                        </span>
                                    </div>
                                @endisset
                                @isset ($document['_source']['data']['image'])
                                    <img alt="Image" src="{{ $document['_source']['data']['image'] }}" style="width: 128px;" class="d-table mb-1" />
                                @endisset
                                @if ($document['_source']['module'] == 'youtube_video')
                                    <img alt="Image" src="https://i.ytimg.com/vi/{{ $document['_source']['data']['id'] }}/hqdefault.jpg" style="width: 128px;" class="d-table mb-1" />
                                @endif
                                @isset ($document['_source']['data']['title'])
                                    <span style="font-size: 20px;">{!! $document['_source']['data']['title'] !!}</span>
                                @endisset
                                @isset ($document['_source']['data']['key'])
                                    <span style="font-size: 32px;">{!! $document['_source']['data']['key'] !!}</span>
                                @endisset
                                @isset ($document['_source']['data']['text'])
                                    <p class="grey-text mb-0">{!! $document['_source']['data']['text'] !!}</p>
                                @endisset
                                @isset ($document['_source']['data']['url'])
                                    <a class="green-text d-table" href="{{ $document['_source']['data']['url'] }}" target="_blank">{{ $document['_source']['data']['url'] }}</a>
                                @endisset
                                @if ($document['_source']['module'] == 'youtube_video')
                                    <a class="green-text d-table" href="https://www.youtube.com/watch?v={{ $document['_source']['data']['id'] }}" target="_blank">https://www.youtube.com/watch?v={{ $document['_source']['data']['id'] }}</a>
                                @endif
                                @if ($document['_source']['module'] == 'twitter_tweet')
                                    <a class="green-text d-table" href="https://twitter.com/{{ $document['_source']['data']['user']['screen_name'] }}/status/{{ $document['_source']['data']['id'] }}" target="_blank">https://twitter.com/{{ $document['_source']['data']['user']['screen_name'] }}/status/{{ $document['_source']['data']['id'] }}</a>
                                @endif
                                @if ($document['_source']['module'] == 'twitter_hashtag')
                                    <a class="green-text d-table" href="https://twitter.com/search?q={{ $document['_source']['data']['key'] }}" target="_blank">https://twitter.com/search?q={{ $document['_source']['data']['key'] }}</a>
                                @endif
                                @if ($document['_source']['module'] == 'instagram_hashtag')
                                    <a class="green-text d-table" href="https://www.instagram.com/explore/tags/{{ $document['_source']['data']['key'] }}/" target="_blank">https://www.instagram.com/explore/tags/{{ $document['_source']['data']['key'] }}/</a>
                                @endif
                            </span>
                        </div>
                    </li>
                @empty
                    <li class="collection-item">
                        @component('components.nothing')
                            @slot('text', 'Bu arşiv kaydedilemedi.')
                        @endcomponent
                    </li>
                @endforelse
            @else
                <li class="collection-item">
                    @component('components.nothing')
                        @slot('text', 'Teknik bir sebepten ötürü bu arşive ulaşılamıyor.')
                    @endcomponent
                </li>
            @endif
        </ul>
    </div>
@endsection
