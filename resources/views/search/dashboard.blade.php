@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Arama'
        ]
    ],
    'dock' => true
])

@push('local.scripts')
    function __search_archive(__, obj)
    {
        var ul = $('#search_archive');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp').attr('data-id', 'list-item-' + o.id)

                        item.html(o.url)

                        item.appendTo(ul)
                })
            }

            $('#home-loader').hide()
        }
    }
@endpush

@section('content')
    <div class="card">
        <div class="card-content grey lighten-5">
            <div class="input-field">
                <input
                    id="string"
                    name="string"
                    type="text"
                    class="validate json json-search"
                    data-json-target="#search_archive" />
                <label for="string">Ara</label>
                <span class="d-flex">
                    <a href="#" class="align-self-center" data-trigger="info" style="margin: 0 .4rem 0 0;">
                        <i class="material-icons">info_outline</i>
                    </a>
                    <span class="align-self-center">Aramak istediğiniz kelimeyi veya kriteri girin.</span>
                </span>
                <span class="helper-text"></span>
            </div>
        </div>
        <ul class="collection json-clear" 
            id="search_archive"
            data-href="{{ route('search.request') }}"
            data-skip="0"
            data-take="10"
            data-more-button="#search_archive-more_button"
            data-callback="__search_archive"
            data-method="post"
            data-include="start_date,end_date,sentiment,full_match,modules,string"
            data-nothing>
            <li class="collection-item nothing hide">
                @component('components.nothing')
                    @slot('cloud_class', 'white-text')
                    @slot('size', 'small')
                    @slot('text', 'Sonuç bulunamadı!')
                    @slot('text_class', 'grey-text')
                @endcomponent
            </li>
            <li class="collection-item model hide"></li>
        </ul>
    </div>

    <div class="center-align">
        <button class="btn-flat waves-effect hide json"
                id="search_archive-more_button"
                type="button"
                data-json-target="ul#search_archive">Daha Fazla</button>
    </div>
@endsection

@include('_inc.alerts.search_operators')

@push('external.include.footer')
    <script src="{{ asset('js/jquery.mark.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
@endpush

@section('wildcard')
    <div class="z-depth-1">
        <div class="container wild-area">
            <div class="wild-content d-flex" data-wild="date">
                <span class="wild-body d-flex">
                    <a href="#" class="btn-floating btn-flat btn-small waves-effect align-self-center mr-1" data-class=".wild-content" data-class-remove="active">
                        <i class="material-icons">close</i>
                    </a>
                    <input style="max-width: 96px;" type="text" class="datepicker" name="start_date" value="{{ date('Y-m-d', strtotime('-1 day')) }}" placeholder="Başlangıç" />
                    <input style="max-width: 96px;" type="text" class="datepicker" name="end_date" value="{{ date('Y-m-d') }}" placeholder="Bitiş" />
                </span>
            </div>
            <div class="wild-content d-flex" data-wild="sentiment">
                <span class="wild-body d-flex">
                    <a href="#" class="btn-floating btn-flat btn-small waves-effect align-self-center mr-1" data-class=".wild-content" data-class-remove="active">
                        <i class="material-icons">close</i>
                    </a>
                    <label class="align-self-center mr-1" data-tooltip="Pozitif">
                        <input type="radio" name="sentiment" value="pos" />
                        <span class="material-icons grey-text text-darken-2">sentiment_satisfied</span>
                    </label>
                    <label class="align-self-center mr-1" data-tooltip="Negatif">
                        <input type="radio" name="sentiment" value="neg" />
                        <span class="material-icons grey-text text-darken-2">sentiment_dissatisfied</span>
                    </label>
                    <label class="align-self-center mr-1" data-tooltip="Nötr">
                        <input type="radio" name="sentiment" value="neu" />
                        <span class="material-icons grey-text text-darken-2">sentiment_neutral</span>
                    </label>
                    <label class="align-self-center mr-1" data-tooltip="Tümü">
                        <input type="radio" name="sentiment" value="all" checked="" />
                        <span class="material-icons grey-text text-darken-2">fullscreen</span>
                    </label>
                </span>
            </div>
            <ul class="wild-menu">
                <li>
                    <a class="d-flex" href="#" data-class="[data-wild=date]" data-class-add="active">
                        <i class="material-icons mr-1">date_range</i>
                        <span class="align-self-center">Tarih</span>
                    </a>
                </li>
                <li>
                    <a class="d-flex" href="#" data-class="[data-wild=sentiment]" data-class-add="active">
                        <i class="material-icons mr-1">mood</i>
                        <span class="align-self-center">Duygu</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
@endsection

@section('dock')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Kaynak</span>
        </div>
        <div class="collection collection-bordered">
            <label class="collection-item waves-effect d-block">
                <input name="full_match" value="on" type="checkbox" />
                <span>Kelimesi Kelimesine</span>
            </label>
            <div class="divider"></div>
            @foreach (config('system.modules') as $key => $module)
                <label class="collection-item waves-effect d-block">
                    <input name="modules" checked value="{{ $key }}" type="checkbox" />
                    <span>{{ $module }}</span>
                </label>
            @endforeach
        </div>
    </div>
@endsection

@push('local.scripts')
    $('.datepicker').datepicker({
        firstDay: 0,
        format: 'yyyy-mm-dd',
        i18n: date.i18n,
        container: 'body'
    })
@endpush
