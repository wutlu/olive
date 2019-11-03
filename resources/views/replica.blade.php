@extends('layouts.app', [
    'sidenav_layout' => true,
    'footer_hide' => true,
    'breadcrumb' => [
        [
            'text' => 'Kopya İçerik'
        ]
    ],
    'wide' => true
])

@push('local.styles')
    #search-area {
        border-width: 0 0 1px;
        border-style: solid;
        border-color: #e1e1e1;
    }
    #search-area [data-trigger] {
        padding: 0 1rem;

        border-width: 0 1px 0 0;
        border-style: solid;
        border-color: #e1e1e1;

        display: table;

        -webkit-transition: all 200ms cubic-bezier(.25, .46, .45, .94);
                transition: all 200ms cubic-bezier(.25, .46, .45, .94);
    }
    #search-area [data-trigger]:active {
        -webkit-box-shadow: inset 0 0 .4rem 0 rgba(0, 0, 0, .1);
                box-shadow: inset 0 0 .4rem 0 rgba(0, 0, 0, .1);
    }
    #search-area #string {
        margin: 0;
        padding: 1rem;
        border-width: 0;

        -webkit-box-shadow: none;
                box-shadow: none;
    }

    #search-tools {
        padding: 6px 1rem;
    }
    #search-tools > .input-field {
        margin: 0;
    }

    #date-area > .input-field > input[type=date] {
        border-width: 0 !important;

        margin: 0 12px !important;

        -webkit-box-shadow: none !important;
                box-shadow: none !important;
    }
@endpush

@push('local.scripts')
    $(document).on('click', '[data-trigger=clear]', function() {
        $('input[name=string]').val('').hide().show( 'highlight', { 'color': '#f0f4c3' }, 400 ).focus()
    })

    $('select').formSelect()
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/jquery.ui.min.js?v='.config('system.version')) }}"></script>
@endpush

@section('wildcard')
    <div class="d-flex" id="search-area">
        <a href="#" class="flex-fill d-flex" data-trigger="clear">
            <i class="material-icons align-self-center">clear</i>
        </a>
        <input
            type="text"
            name="string"
            id="string"
            placeholder="En Az 2 Kelime Başlık Girin"
            class="json-search json"
            data-json-target="ul#search" />
    </div>

    <div class="d-flex justify-content-between" id="search-tools">
        <div class="d-flex justify-content-start flex-wrap">
            <div class="input-field m-0 align-self-center">
                <select name="smilarity" id="smilarity">
                    <option value="100">100%</option>
                    <option value="90">90%</option>
                    <option value="80">80%</option>
                    <option value="70">70%</option>
                    <option value="60">60%</option>
                    <option value="50" selected>50%</option>
                    <option value="40">40%</option>
                    <option value="30">30%</option>
                    <option value="20">20%</option>
                    <option value="10">10%</option>
                </select>
                <small class="helper-text">Benzerlik Oranı</small>
            </div>
            <div class="input-field m-0 align-self-center">
                <select name="source" id="source">
                    <option value="news">Haber</option>
                </select>
                <small class="helper-text">Kaynak</small>
            </div>
        </div>
        <div class="d-flex justify-content-end flex-wrap" id="date-area">
            <div class="input-field m-0 align-self-center">
                <input style="width: 128px;" type="date" name="start_date" value="{{ date('Y-m-d', strtotime('-1 day')) }}" />
            </div>
            <div class="input-field m-0 align-self-center">
                <input style="width: 128px;" type="date" name="end_date" value="{{ date('Y-m-d') }}" />
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="card card-unstyled">
        <ul class="collection collection-unstyled json-clear loading" 
            id="search"
            data-href="{{ route('replica.dashboard') }}"
            data-skip="0"
            data-take="10"
            data-more-button="#search-more_button"
            data-callback="__search_archive"
            data-method="post"
            data-include="{{ $elements }}"
            data-nothing>
            <li class="collection-item nothing">
                <div class="olive-alert info">
                    <div class="anim"></div>
                    <h4 class="mb-2">Kopya İçerik</h4>
                    <p>Bir yazının tüm kopyalarını tespit edebilmek için hemen bir arama yapın!</p>
                </div>
            </li>
            <li class="collection-item model hide"></li>
        </ul>
    </div>
    <a href="#"
       class="more hide json"
       id="search-more_button"
       data-json-target="ul#search">Daha Fazla</a>
@endsection

@push('local.scripts')
    function __search(__, obj)
    {
        if (obj.status == 'ok')
        {
            alert(1)
        }
    }
@endpush
