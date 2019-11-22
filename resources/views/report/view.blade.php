@extends('layouts.app', [
    'title' => [
        'text' => $report->password ? 'Şifreli Rapor' : $report->name
    ],
    'footer_hide' => true,
    'robots' => $report->password ? [ 'noindex' ] : false
])

@section('content')
    @if ($authenticate === null || $authenticate === true)
        <div id="fullpage">
            <div class="section cyan darken-4">
                <div class="report-view">
                    <div class="hide-on-1024-and-up">
                        <span class="white-text d-table mx-auto p-2 center-align">Bu sayfa sadece geniş ekranlı cihazlar ile görüntülenebilir.</span>
                    </div>
                    <div class="report-page">
                        <div class="sphere sphere-2"></div>

                        <header>
                            <img class="logo" alt="Logo" src="{{ asset('img/olive_logo.svg') }}" />
                            <div class="date">
                                <small>{{ $report->date_1 ? date('d.m.Y', strtotime($report->date_1)) : '' }}</small>
                                <small>{{ $report->date_2 ? date('d.m.Y', strtotime($report->date_2)) : '' }}</small>
                            </div>
                            <h1 class="page-title">{{ $report->name }}</h1>
                        </header>

                        <img class="logo" alt="Logo" src="{{ asset('img/veri.zone_logo.svg') }}" />
                    </div>
                </div>
            </div>
            @foreach ($report->pages as $page)
                <div class="section" id="section-{{ $page->id }}">
                    <div class="report-view">
                        <div class="hide-on-1024-and-up">
                            <span class="d-table mx-auto p-2 center-align">Bu sayfa sadece geniş ekranlı cihazlar ile görüntülenebilir.</span>
                        </div>
                        <div class="report-page">
                            <h3 class="title">{{ $page->title }}</h3>
                            <h6 class="subtitle">{{ $page->subtitle }}</h6>
                            <div class="date">
                                <small>{{ $report->date_1 ? date('d.m.Y', strtotime($report->date_1)) : '' }}</small>
                                <small>{{ $report->date_2 ? date('d.m.Y', strtotime($report->date_2)) : '' }}</small>
                            </div>
                            <div class="logo">
                                <img alt="Logo" src="{{ asset('img/olive_logo.svg') }}" />
                            </div>
                            <div class="content d-flex align-self-stretch"></div>
                        </div>
                    </div>
                </div>

                @push('local.scripts')
                    __report__pattern({!! $page->pattern() !!}, $('#section-{{ $page->id }}'), '{{ explode('.', $page->type)[1] }}', 'read')
                @endpush
            @endforeach
            <div class="section cyan darken-4">
                <div class="report-view">
                    <div class="hide-on-1024-and-up">
                        <span class="white-text d-table mx-auto p-2 center-align">Bu sayfa sadece geniş ekranlı cihazlar ile görüntülenebilir.</span>
                    </div>
                    <div class="report-page">
                        <div class="sphere sphere-center sphere-2"></div>

                        <footer>
                            <div class="ground left">
                                <a href="mailto:bilgi@veri.zone">bilgi@veri.zone</a>
                                <a href="tel:850-302-1631">(+90) 850 302 16 30</a>
                                <a href="https://veri.zone/">https://veri.zone</a>
                                <a href="https://olive.veri.zone/">https://olive.veri.zone</a>
                                <p>Mustafa Kemal Mh. Dumlupınar Blv. ODTÜ Teknokent Bilişim İnovasyon Merkezi 280/G No: 1260 Alt Zemin Kat Çankaya, Ankara</p>
                                <img class="logo" alt="Logo" src="{{ asset('img/olive_logo.svg') }}" />
                            </div>
                            <div class="ground center">
                                <img class="logo" alt="Logo" src="{{ asset('img/veri.zone_logo.svg') }}" />
                                <span>v e r i . z o n e . t e k n o l o j i</span>
                            </div>
                            <div class="ground right">
                                <a target="_blank" href="https://twitter.com/verizonetek">Twitter @verizonetek</a>
                                <a target="_blank" href="https://www.linkedin.com/company/verizonetek/">Linkedin @verizonetek</a>
                                <a target="_blank" href="https://www.instagram.com/verizonetek/">Instagram @verizonetek</a>
                            </div>
                        </footer>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div id="fullpage">
            <div class="section">
                <div class="card d-table mx-auto">
                    <div class="card-content p-2">
                        <span class="card-title">Şifreli Rapor</span>
                        <form method="post" action="{{ route('report.view', $report->key) }}">
                            @csrf
                            <div class="input-field">
                                <input name="password" id="password" type="password" class="validate" />
                                <label for="password">Rapor Şifresi</label>
                            </div>
                            <div class="input-field">
                                <div class="captcha" data-id="register-captcha"></div>
                            </div>
                            @if ($errors->any())
                                <ul class="mb-1">
                                    @foreach ($errors->all() as $error)
                                        <li class="red-text">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="grey-text text-darken-4 mb-1">
                                    @component('components.alert')
                                        @slot('icon', 'info')
                                        @slot('text', 'Bu rapor şifrelenmiş.<br />Görüntülemek için lütfen rapor şifresini girin.')
                                    @endcomponent
                                </div>
                            @endif
                            <div class="d-flex justify-content-between">
                                <img class="align-self-center" alt="Logo" src="{{ asset('img/olive_logo-grey.svg') }}" style="width: auto; height: 32px;" />
                                <button type="submit" class="btn-flat waves-effect align-self-center">Raporu Aç</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('local.scripts')
    var myFullpage = new fullpage('#fullpage', {
        navigation: {{ $authenticate === null || $authenticate === true ? ($report->pages->count() <= 30 ? 'true' : 'false') : 'false' }},
        navigationPosition: 'right',
        scrollBar: {{ $report->pages->count() <= 10 ? 'false' : 'true' }},
        anchors: {!! json_encode($anchors) !!},
        showActiveTooltip: true,
        navigationTooltips: {!! json_encode($titles) !!}
    })
@endpush

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/fullpage.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src='//www.google.com/recaptcha/api.js'></script>
    <script src="{{ asset('js/fullpage.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/speakingurl.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
@endpush

@push('local.styles')

    #fp-nav ul li .fp-tooltip {
        color: #111;
    }
@endpush
