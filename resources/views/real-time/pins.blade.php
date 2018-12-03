@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Gerçek Zamanlı',
            'link' => route('realtime')
        ],
        [
            'text' => $pin_group->name,
            'link' => route('realtime.stream', $pin_group->id)
        ],
        [
            'text' => 'Pinlemeler'
        ]
    ]
])

@push('local.styles')
    textarea[name=comment] {
        margin: 0;
        border-width: 0 !important;
        box-shadow: none !important;
    }

    .document .card-title,
    .document p {
        margin: 0 !important;
    }

    .sentiment-analysis {
        margin: 0 1rem 0 0;
        min-width: 100px;
    }
@endpush

@section('content')

    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/card-header.jpg') }}" alt="Pinlemeler" />
            <span class="card-title">Pinlemeler</span>
            <a
                href="{{ route('crawlers.media.bot') }}"
                class="btn-floating btn-large halfway-fab waves-effect white"
                data-tooltip="Pdf Dökümü Al"
                data-position="left"
                style="background-image: url('{{ asset('img/pdf.svg') }}');"></a>
        </div>
        <div class="card-content red lighten-5">
            Ekleyeceğiniz yorumlar, PDF çıktılarda analiz sonucu olarak yer alacaktır.
        </div> 
    @forelse ($pins as $pin)
        @php
        $document = $pin->document();
        @endphp

        @if ($document->status == 'ok')
            @php
            $sentiment = $document->data['_source']['sentiment'];

            @endphp
            <div class="card-content document z-depth-1 hoverable">
                <time class="grey-text d-block right-align">{{ date('d.m.Y H:i:s', strtotime($document->data['_source']['created_at'])) }}</time>
                <div class="d-flex">
                    <ul class="sentiment-analysis">
                        <li class="d-flex justify-content-between green-text">
                            <i class="material-icons align-self-center">sentiment_very_satisfied</i>
                            <span class="align-self-center">{{ $sentiment['pos']*100 }}%</span>
                        </li>
                        <li class="d-flex justify-content-between grey-text">
                            <i class="material-icons align-self-center">sentiment_neutral</i>
                            <span class="align-self-center">{{ $sentiment['neu']*100 }}%</span>
                        </li>
                        <li class="d-flex justify-content-between red-text">
                            <i class="material-icons align-self-center">sentiment_very_dissatisfied</i>
                            <span class="align-self-center">{{ $sentiment['neg']*100 }}%</span>
                        </li>
                    </ul>
                    <div> 
                        @if ($document->data['_type'] == 'tweet')
                            <a href="#" class="cyan-text">{{ $document->data['_source']['user']['name'] }} {{ '@'.$document->data['_source']['user']['screen_name'] }}</a>
                            <p>
                                <a href="https://twitter.com/{{ $document->data['_source']['user']['screen_name'] }}/{{ $document->data['_source']['id'] }}" target="_blank" class="grey-text">https://twitter.com/{{ $document->data['_source']['user']['screen_name'] }}/{{ $document->data['_source']['id'] }}</a>
                            </p>
                            <p>{!! nl2br($document->data['_source']['text']) !!}</p>
                        @elseif ($document->data['_type'] == 'video')
                            <a href="#" class="card-title">{{ $document->data['_source']['title'] }}</a>
                            <a href="#" class="cyan-text">{{ '@'.$document->data['_source']['channel']['title'] }}</a>
                            <p>
                                <a href="https://www.youtube.com/watch?v={{ $document->data['_source']['id'] }}" target="_blank" class="grey-text">https://www.youtube.com/watch?v={{ $document->data['_source']['id'] }}</a>
                            </p>
                            @isset($document->data['_source']['description'])
                                <p>{!! nl2br($document->data['_source']['description']) !!}</p>
                            @endisset
                        @elseif ($document->data['_type'] == 'comment')
                        yorum
                        @elseif ($document->data['_type'] == 'entry')
                            <a href="#" class="card-title">{{ $document->data['_source']['title'] }}</a>
                            <a href="#" class="cyan-text">{{ '@'.$document->data['_source']['author'] }}</a>
                            <p>
                                <a href="{{ $document->data['_source']['url'] }}" target="_blank" class="grey-text">{{ $document->data['_source']['url'] }}</a>
                            </p>
                            <p>{!! nl2br($document->data['_source']['entry']) !!}</p>
                        @elseif ($document->data['_type'] == 'article')
                        haber
                        @elseif ($document->data['_type'] == 'product')
                        ürün
                        @else
                        test
                        @endif
                    </div>
                </div>

                <div class="input-field mb-0">
                    <textarea id="textarea-{{ $pin->index.'-'.$pin->type.'-'.$pin->id }}" name="comment" class="materialize-textarea"></textarea>
                    <label for="textarea-{{ $pin->index.'-'.$pin->type.'-'.$pin->id }}">Yorum Girin</label>
                </div>
            </div>
        @else
            <div class="card-content">
                <div class="not-found">
                    <i class="material-icons">cloud_off</i>
                    <i class="material-icons">cloud_off</i>
                    <i class="material-icons red-text">sentiment_very_dissatisfied</i>
                    <p>Kaynak Okunamadı</p>
                </div>
            </div>
        @endif
    @empty
        <div class="card-content">
            <div class="not-found">
                <i class="material-icons">cloud</i>
                <i class="material-icons">cloud</i>
                <i class="material-icons">wb_sunny</i>
                <p>Pinleme Yok</p>
            </div>
        </div>
    @endif
    </div>

    {!! $pins->links('vendor.pagination.materializecss') !!}

@endsection
