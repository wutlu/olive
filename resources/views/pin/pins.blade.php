@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Pin Grupları',
            'link' => route('pin.groups')
        ],
        [
            'text' => $pg->name
        ]
    ]
])

@push('local.scripts')
    function __pdf(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({
                html: 'Rapor isteğiniz alındı.<br />Biz raporunuzu hazırlarken,<br />araştırmanıza devam edebilirsiniz.',
                classes: 'green darken-2'
            })
        }
    }
@endpush

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/card-header.jpg') }}" alt="Pinlemeler" />
            <span class="card-title">Pinlemeler</span>
            <a
                href="#"
                class="btn-floating btn-large halfway-fab waves-effect white json btn-image"
                data-tooltip="Pdf Dökümü Al"
                data-position="left"
                data-href="{{ route('pin.pdf') }}"
                data-id="{{ $pg->id }}"
                data-method="post"
                data-callback="__pdf"
                style="background-image: url('{{ asset('img/icons/pdf.png') }}');"></a>
        </div>
        <div class="card-content">
            Ekleyeceğiniz yorumlar, PDF raporlarınızda analiz sonucu olarak yer alacaktır.
        </div>
        @if ($pg->html_to_pdf == 'success')
            <div class="card-action d-flex justify-content-between">
                <span class="align-self-center">{{ date('d.m.Y H:i', strtotime($pg->completed_at)) }}</span>
                <a href="{{ url($pg->pdf_path) }}" class="btn-flat waves-effect align-self-center">Pdf İndir</a>
            </div>
        @endif
    </div>

    @forelse ($pins as $pin)

        @php
        $document = $pin->document();
        @endphp

        @if ($document->status == 'ok')
            @php
            $id = $document->data['_index'].'_'.$document->data['_type'].'_'.$document->data['_id'];
            $type = $document->data['_type'];
            $source = $document->data['_source'];

            $sentiment = @$source['sentiment'];
            @endphp

            <ul id="dropdown-{{ $id }}" class="dropdown-content">
                @if ($type == 'tweet')
                    <li>
                        <a href="#" class="waves-effect">Kullanıcının Alınan Tüm Tweetleri</a>
                    </li>
                    <li>
                        <a href="#" class="waves-effect">Kullanıcı Bilgileri</a>
                    </li>
                @elseif ($type == 'article')
                    <li>
                        <a href="#" class="waves-effect">Sitenin Tüm Haberleri</a>
                    </li>
                    <li>
                        <a href="#" class="waves-effect">Site Bilgileri</a>
                    </li>
                @elseif ($type == 'entry')
                    <li>
                        <a href="#" class="waves-effect">Başlığa Git</a>
                    </li>
                    <li>
                        <a href="#" class="waves-effect">Kullanıcının Tüm Girdileri</a>
                    </li>
                    <li>
                        <a href="#" class="waves-effect">Kullanıcı Bilgileri</a>
                    </li>
                @elseif ($type == 'product')
                    <li>
                        <a href="#" class="waves-effect">Kullanıcının Tüm Ürünleri</a>
                    </li>
                @elseif ($type == 'comment')
                    <li>
                        <a href="#" class="waves-effect">Kullanıcının Tüm Yorumları</a>
                    </li>
                    <li>
                        <a href="#" class="waves-effect">Kullanıcının Trend'e Giren Videoları</a>
                    </li>
                    <li>
                        <a href="#" class="waves-effect">Video'ya Yapılan Diğer Yorumlar</a>
                    </li>
                @elseif ($type == 'video')
                    <li>
                        <a href="#" class="waves-effect">Kullanıcının Trend'e Giren Videoları</a>
                    </li>
                    <li>
                        <a href="#" class="waves-effect">Video'ya Yapılan Yorumlar</a>
                    </li>
                @endif
                <li class="divider" tabindex="-1"></li>
                <li>
                    <a href="{{ route('elasticsearch.document', [
                        'index' => $document->data['_index'],
                        'type' => $document->data['_type'],
                        'id' => $document->data['_id']
                    ]) }}" class="waves-effect">İçeriği Detaylı İncele</a>
                </li>
                <li class="divider" tabindex="-1"></li>
                <li>
                    <a
                        href="#"
                        class="waves-effect json"
                        data-href="{{ route('pin', 'remove') }}"
                        data-method="post"
                        data-id="{{ $document->data['_id'] }}"
                        data-type="{{ $document->data['_type'] }}"
                        data-index="{{ $document->data['_index'] }}"
                        data-group_id="{{ $pg->id }}"
                        data-callback="__pin">Pin'i Kaldır</a>
                </li>
            </ul>

            <div class="card card-data {{ $type }} hoverable" data-id="card-{{ $id }}">
                <div class="card-content">
                    <span class="card-title">
                        {{ $type }}
                        <a href="#" class="dropdown-trigger right" data-target="dropdown-{{ $id }}" data-align="right">
                            <i class="material-icons">more_vert</i>
                        </a>
                    </span>

                    <time class="grey-text d-block">{{ date('d.m.Y H:i:s', strtotime($source['created_at'])) }}</time>

                    @isset ($source['title'])
                        <h6 class="teal-text">{{ $source['title'] }}</h6>
                    @endisset

                    @if ($type == 'tweet')
                        <a href="https://twitter.com/{{ $source['user']['screen_name'] }}/status/{{ $source['id'] }}" target="_blank">https://twitter.com/{{ $source['user']['screen_name'] }}/status/{{ $source['id'] }}</a>
                        <p>
                            <a href="https://twitter.com/intent/user?user_id={{ $source['user']['id'] }}" target="_blank" class="red-text">{{ '@'.$source['user']['screen_name'] }}</a>
                            <span class="grey-text">{{ $source['user']['name'] }}</span>
                            <span class="grey-text">({{ $source['platform'] }})</span>
                        </p>
                        <div class="text grey-text text-darken-2">{!! nl2br($source['text']) !!}</div>
                    @elseif ($type == 'article')
                        <a href="{{ $source['url'] }}" target="_blank">{{ str_limit($source['url'], 96) }}</a>
                        <div class="text grey-text text-darken-2">{!! nl2br($source['description']) !!}</div>
                    @elseif ($type == 'entry')
                        <a href="{{ $source['url'] }}" target="_blank">{{ str_limit($source['url'], 96) }}</a>
                        <div class="text grey-text text-darken-2">{!! nl2br($source['entry']) !!}</div>
                    @elseif ($type == 'product')
                        @isset ($source['address'])
                        <ul class="horizontal">
                            @foreach ($source['address'] as $key => $segment)
                                <li class="grey-text" data-icon="»">{{ $segment['segment'] }}</li>
                            @endforeach
                        </ul>
                        @endisset
                        @isset ($source['breadcrumb'])
                        <ul class="horizontal">
                            @foreach ($source['breadcrumb'] as $key => $segment)
                                <li class="grey-text" data-icon="»">{{ $segment['segment'] }}</li>
                            @endforeach
                        </ul>
                        @endisset
                        <a href="{{ $source['url'] }}" target="_blank">{{ str_limit($source['url'], 96) }}</a>
                        <p>
                            <span class="red-text">{{ title_case($source['seller']['name']) }}</span>
                            @isset ($source['seller']['phones'])
                                <ul class="horizontal"> 
                                    @foreach ($source['seller']['phones'] as $key => $phone)
                                        <li class="grey-text" data-icon="|">{{ $phone['phone'] }}</li>
                                    @endforeach
                                </ul>
                            @endisset
                        </p>
                        @isset ($source['description'])
                            <div class="text grey-text text-darken-2">{!! nl2br($source['description']) !!}</div>
                        @endisset
                        <p>
                            <span class="grey-text text-darken-4">{{ number_format($source['price']['amount']) }}</span>
                            <span class="grey-text">{{ $source['price']['currency'] }}</span>
                        </p>
                    @elseif ($type == 'comment')
                        <a href="https://www.youtube.com/watch?v={{ $source['video_id'] }}" target="_blank">https://www.youtube.com/watch?v={{ $source['video_id'] }}</a>
                        <p>
                            <a href="https://www.youtube.com/channel/{{ $source['channel']['id'] }}" target="_blank" class="red-text">{{ '@'.$source['channel']['title'] }}</a>
                        </p>
                        <div class="text grey-text text-darken-2">{!! nl2br($source['text']) !!}</div>
                    @elseif ($type == 'video')
                        <a href="https://www.youtube.com/watch?v={{ $source['id'] }}" target="_blank">https://www.youtube.com/watch?v={{ $source['id'] }}</a>
                        <p>
                            <a href="https://www.youtube.com/channel/{{ $source['channel']['id'] }}" target="_blank" class="red-text">{{ '@'.$source['channel']['title'] }}</a>
                        </p>
                        @isset ($source['description'])
                            <div class="text grey-text text-darken-2">{!! nl2br($source['description']) !!}</div>
                        @endisset
                    @endif
                </div>
                @isset ($source['external'])
                    <div class="card-content">
                        @php
                        $external_source = $pin->document($source['external']['id']);
                        @endphp

                        @if ($external_source)
                            <ul class="collapsible">
                                <li>
                                    <div class="collapsible-header">
                                        <span>
                                            <span class="red-text">{{ '@'.$external_source['_source']['user']['screen_name'] }}</span>
                                            <span class="grey-text">{{ $external_source['_source']['user']['name'] }}</span>
                                            <span class="grey-text">({{ $external_source['_source']['platform'] }})</span>
                                        </span>
                                    </div>
                                    <div class="collapsible-body">
                                        <div style="padding: 24px;">
                                            <a href="https://twitter.com/{{ $external_source['_source']['user']['screen_name'] }}/status/{{ $external_source['_source']['id'] }}" target="_blank">https://twitter.com/{{ $external_source['_source']['user']['screen_name'] }}/status/{{ $external_source['_source']['id'] }}</a>
                                            <div class="text grey-text text-darken-2">{!! nl2br($external_source['_source']['text']) !!}</div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        @endif
                    </div>
                @endif
                <div class="card-comment">
                    <div class="input-field">
                        <textarea
                            id="textarea-{{ $id }}"
                            name="comment"
                            class="materialize-textarea json"
                            data-href="{{ route('pin.comment') }}"
                            data-method="post"
                            data-index="{{ $document->data['_index'] }}"
                            data-type="{{ $document->data['_type'] }}"
                            data-id="{{ $document->data['_id'] }}">{{ $pin->comment }}</textarea>
                        <label for="textarea-{{ $id }}">Yorum Girin</label>
                    </div>
                </div>

                @if ($sentiment)
                    <div class="card-sentiment d-flex justify-content-between">
                        <div style="width: {{ $sentiment['pos']*100 }}%;" class="sentiment-item light-green-text accent-4 d-flex">
                            @if ($sentiment['pos'] > 0.2)
                            <i class="material-icons light-green-text align-self-center">sentiment_very_satisfied</i>
                            <span class="badge light-green-text align-self-center">{{ $sentiment['pos']*100 }}%</span>
                            @endif
                        </div>
                        <div style="width: {{ $sentiment['neu']*100 }}%;" class="sentiment-item grey-text d-flex">
                            @if ($sentiment['neu'] > 0.2)
                            <i class="material-icons grey-text text-darken-2 align-self-center">sentiment_neutral</i>
                            <span class="badge grey-text text-darken-2 align-self-center">{{ $sentiment['neu']*100 }}%</span>
                            @endif
                        </div>
                        <div style="width: {{ $sentiment['neg']*100 }}%;" class="sentiment-item red-text accent-4 d-flex">
                            @if ($sentiment['neg'] > 0.2)
                            <i class="material-icons red-text align-self-center">sentiment_very_dissatisfied</i>
                            <span class="badge red-text align-self-center">{{ $sentiment['neg']*100 }}%</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="card-panel red">
                @component('components.nothing')
                    @slot('cloud_class', 'white-text')
                    @slot('cloud', 'cloud_off')
                    @slot('sun', 'sentiment_very_dissatisfied')
                    @slot('text', 'Kaynak Okunamadı')
                @endcomponent
            </div>
        @endif
    @empty
        @component('components.nothing')
            @slot('cloud_class', 'white-text')
            @slot('text', 'Pinleme Yok')
        @endcomponent
    @endforelse

    {!! $pins->links('vendor.pagination.materializecss') !!}

@endsection

@push('local.scripts')
    function __pin(__, obj)
    {
        if (obj.status == 'removed')
        {
            __.closest('.card.card-data').slideUp();
        }
        else if (obj.status == 'failed')
        {
            M.toast({ html: 'Hay aksi, beklenmedik bir durum.', classes: 'orange darken-2' })
        }
    }
@endpush
