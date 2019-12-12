@extends('layouts.app', [
    'title' => [
        'text' => $report->password ? 'Şifreli Rapor' : $report->name
    ],
    'footer_hide' => true,
    'robots' => $report->password ? [ 'noindex' ] : false,
    'desktop' => $authenticate === null || $authenticate === true ? true : false,
    'search_hide' => true
])

@push('local.styles')
    .header,
    .footer {
        height: 100vh;
        background-color: #006064;
    }
@endpush

@push('local.scripts')
    $(document).on('click', '[data-trigger=print]', function() {
        window.print()
    })

    setTimeout(function() {
        $('[data-trigger=print]').removeAttr('disabled')
    }, 2000)
@endpush

@section('content')
    <div id="fullpage">
        @if ($authenticate === null || $authenticate === true)
            @push('local.styles')
                body {
                    overflow: auto !important;
                }
            @endpush
            <div class="section header d-flex justify-content-center">
                <div class="report-view align-self-center">
                    <div class="report-page">
                        <div class="sphere sphere-2"></div>

                        <header>
                            <img class="logo" alt="Logo" src="{{ asset('img/olive_logo.svg') }}" />
                            <div class="date">
                                <small>{{ $report->date_1 ? date('d.m.Y', strtotime($report->date_1)) : '' }}</small>
                                <small>{{ $report->date_2 ? date('d.m.Y', strtotime($report->date_2)) : '' }}</small>
                            </div>
                            <h1 class="page-title">{{ $report->name }}</h1>

                            <a href="#" data-trigger="print" disabled class="btn-floating btn-flat no-print">
                                <i class="material-icons">print</i>
                            </a>
                        </header>

                        <img class="logo" alt="Logo" src="{{ asset('img/veri.zone_logo.svg') }}" />
                    </div>
                </div>
            </div>
            @foreach ($report->pages as $page)
                <div class="section d-flex justify-content-center" id="section-{{ $page->id }}">
                    <div class="report-view align-self-center">
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
                            <div class="content d-flex justify-content-center align-self-stretch"></div>
                        </div>
                    </div>
                </div>

                @push('local.scripts')
                    __report__pattern({!! $page->pattern() !!}, $('#section-{{ $page->id }}'), '{{ explode('.', $page->type)[1] }}', 'read')
                @endpush
            @endforeach
            <div class="section footer d-flex justify-content-center">
                <div class="report-view align-self-center">
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
        @else
            <div class="section">
                <div class="card card-unstyled d-table mx-auto mb-1">
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
                <p class="grey-text center-align">{{ date('Y') }} © <a href="https://veri.zone/" class="grey-text">Veri Zone Teknoloji</a></p>
            </div>
        @endif
    </div>
@endsection

@push('local.styles')
    @media print {
        @page { 
            size: A4 portrait;
            margin: 0;
            padding: 0;
        }
        body {
            margin: 0;
            padding: 0;

            -webkit-transform: scale(1, 1); 
               -moz-transform: scale(1, 1);
        }
        .section {
            width: 100vh;
            height: 100vw;
        }
        .report-view {
            margin: 0;
            padding: 0;
        }
        .report-page {
            border: 2px solid #ccc;

            -webkit-box-shadow: none;
                    box-shadow: none;
        }
        .no-print,
        .no-print * {
            display: none !important;
        }
        .tr-map > small.state {
            background-color: #fff !important;
        }
        .report-page,
        .tr-map,
        .tr-map > small.state:before,
        .tr-map > small.state:after,
        .tr-map > small.state {
            -webkit-print-color-adjust: exact !important;
                          color-adjust: exact !important;
        }
    }
@endpush

@push('external.include.header')
    <link media="print" rel="Alternate" href="print.pdf" />
    <link rel="stylesheet" href="{{ asset('css/jquery.cloud.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src='//www.google.com/recaptcha/api.js'></script>
    <script src="{{ asset('js/speakingurl.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
    <script src="{{ asset('js/jquery.cloud.min.js?v='.config('system.version')) }}"></script>
@endpush
