@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Kelime Hafızası',
            'link' => route('analysis.dashboard')
        ],
        [
            'text' => $module['title'],
        ]
    ],
    'dock' => true,
    'footer_hide' => true
])

@section('dock')
    <div class="card">
        <div class="card-content">
            <span class="card-title">Grup</span>
        </div>
        <div class="collection">
            @foreach ($module['types'] as $key => $type)
                <label class="collection-item waves-effect d-block">
                    <input name="group" type="radio" value="{{ $key }}" data-update="true" />
                    <span>{{ $type['title'] }}</span>
                </label>
            @endforeach

            <div class="divider"></div>

            <label class="collection-item waves-effect d-block">
                <input name="saver" type="checkbox" value="on" />
                <span>Enter tuşu ile aramadaki kelimeyi kaydet.</span>
            </label>

            <div class="divider"></div>

            <a
                class="collection-item waves-effect json"
                href="#"
                data-include="group"
                data-module="{{ $module_name }}"
                data-method="post"
                data-loader="#compiler-loader"
                data-href="{{ route('analysis.group.compile') }}"
                data-callback="__compile"
                data-callbefore="__compile_before">Seçili Grubu Derle</a>
        </div>

        @component('components.loader')
            @slot('color', 'cyan')
            @slot('class', 'hide card-loader-unstyled')
            @slot('id', 'compiler-loader')
        @endcomponent
    </div>
@endsection

@push('local.scripts')
    function __compile_before(__)
    {
        $('#compiler-loader').removeClass('hide')
        __.addClass('disabled')
    }

    function __compile(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({
                html: 'Grup Derlendi',
                classes: 'green darken-2'
            })

            __.removeClass('disabled')

            var search = $('#collections');
                search.data('skip', 0).addClass('json-clear');

            vzAjax(search)
        }
    }

    function __test(__, obj)
    {
        if (obj.status == 'ok')
        {
            var progress_collection = $('[data-name=test-progress]');
                progress_collection.removeClass('hide')
                progress_collection.children(':not(.model)').remove();

            var model = progress_collection.find('.collection-item.model');

            $.each(obj.data, function(key, o) {
                var item = model.clone();
                    item.removeClass('hide model')

                    item.find('[data-name=text]').html(o.text)

                    $.each(o.data, function(k, x) {
                        var k = k.split('-');

                        var li = $('<li />', {
                            'class': 'collection-item',
                            'html': [
                                $('<span />', { 'html': k[1] }),
                                $('<span />', {
                                    'class': 'badge grey',
                                    'html': x
                                })
                            ]
                        })

                        item.append(li)
                    })

                    item.appendTo(progress_collection)
            })
        }
    }
@endpush

@section('content')
    <div class="card mb-1">
        <div class="card-content">
            <span class="card-title">Test Alanı</span>
            <div class="input-field">
                <textarea id="testarea" name="testarea" class="materialize-textarea validate" data-length="10000"></textarea>
                <label for="testarea">Test Metni</label>
                <span class="helper-text">Her bir satır için farklı bir test metni girebilirsiniz.</span>
            </div>
            <div class="right-align">
                <a
                    href="#"
                    data-href="{{ route('analysis.module.test') }}"
                    data-include="testarea"
                    data-method="post"
                    data-callback="__test"
                    data-engine="{{ $module_name }}"
                    class="btn-flat waves-effect json">Testi Çalıştır</a>
            </div>
        </div>
        <ul class="collection hide" data-name="test-progress">
            <li class="collection-item model hide">
                <span data-name="text"></span>
                <ul data-name="per" class="collection"></ul>
            </li>
        </ul>
    </div>
    <div class="card">
        <div class="card-content">
            <span class="card-title">{{ $module['title'] }}</span>
            <span class="grey-text text-darken-2" data-indicator="total">0</span>
        </div>
        <nav class="nav-half">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#collections"
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                </div>
            </div>
        </nav>
        <div class="card-content">
            <div class="json-clear load d-flex flex-wrap" 
                 id="collections"
                 data-href="{{ route('analysis.module.words') }}"
                 data-skip="0"
                 data-take="100"
                 data-module="{{ $module_name }}"
                 data-include="string,group"
                 data-more-button="#collections-more_button"
                 data-callback="__collection"
                 data-method="post"
                 data-loader="#home-loader"
                 data-nothing>
                <div class="nothing hide" style="width: 100%;">
                    @component('components.nothing')
                        @slot('size', 'small')
                    @endcomponent
                </div>
                <a href="#" class="model hide chip waves-effect" data-trigger="delete"></a>
            </div>
        </div>

        @component('components.loader')
            @slot('color', 'cyan')
            @slot('id', 'home-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>

    <a href="#"
       class="more hide json"
       id="collections-more_button"
       data-json-target="#collections">Daha Fazla</a>

    <ul class="collection mt-1">
        <li class="collection-item grey-text d-flex p-0">
            <i class="material-icons align-self-center">info_outline</i>
            <span class="align-self-center">Kelime listeleri indexlenmiş olarak, en yalın halde tutulur.</span>
        </li>
        <li class="collection-item grey-text d-flex p-0">
            <i class="material-icons align-self-center">info_outline</i>
            <span class="align-self-center">Birden fazla kelimeden oluşan durumlarda, kelimeleri gruplarına göre mantıksal olarak eklemelisiniz.</span>
        </li>
        <li class="collection-item grey-text d-flex p-0">
            <i class="material-icons align-self-center">info_outline</i>
            <span class="align-self-center">Tekrar eden harfler derleme esnasında teke düşürülür.</span>
        </li>
    </ul>
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/shapeChaos.min.js?v='.config('system.version')) }}"></script>
@endpush
@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/shapeChaos.css?v='.config('system.version')) }}" />
@endpush

@section('wildcard')
    <div class="shapeChaos teal" style="height: 48px;"></div>
@endsection

@push('local.scripts')
    $(document).on('change', '[data-update]', function() {
        var search = $('#collections');
            search.data('skip', 0).addClass('json-clear');

        vzAjax(search)
    })

    $('.shapeChaos').shapeChaos({
        'num_shapes': 200,
        'classes': [ 'sc_square' ],
        'colors': [
            '#00695c',
            '#00796b',
            '#00897b',
            '#009688'
        ]
    })

    function __collection(__, obj)
    {
        var ul = __;
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp d-flex')

                        item.html(o.word)
                            .data('id', o.id)
                            .addClass(o.compiled ? 'green' : (o.learned ? 'cyan' : 'red'))

                        item.appendTo(ul)
                })
            }

            $('[data-indicator=total]').html(obj.total)
        }
    }

    $(document).on('click', '[data-trigger=delete]', function() {
        return modal({
            'id': 'alert',
            'body': 'Silmek istediğinizden emin misiniz?',
            'size': 'modal-small',
            'title': 'Sil',
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect grey-text btn-flat',
                    'html': buttons.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<a />', {
                    'href': '#',
                    'class': 'waves-effect btn-flat red-text json',
                    'html': buttons.ok,
                    'data-href': '{{ route('analysis.module.word.delete') }}',
                    'data-method': 'delete',
                    'data-id': $(this).data('id'),
                    'data-callback': '__delete'
                })
            ],
            'options': {}
        })
    }).on('keyup', '#string', function(e) {
        if (e.keyCode == 13 && $('input[name=saver]').is(':checked'))
        {
            vzAjax($('<div />', {
                'data-include': 'string,group',
                'data-module': '{{ $module_name }}',
                'data-method': 'post',
                'data-href': '{{ route('analysis.module.word.create') }}',
                'data-callback': '__create'
            }))
        }
    })

    function __delete(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('[data-id=' + obj.data.id + '].collection-item').remove()

            $('#modal-alert').modal('close')

            setTimeout(function() {
                $('#modal-form').modal('close')
            }, 200)

            M.toast({
                html: 'Kelime Silindi',
                classes: 'green darken-2'
            })

            var collection = $('#collections');
                collection.data('skip', 0).addClass('json-clear');

            vzAjax(collection)
        }
    }

    function __create(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({
                html: 'Kelime Oluşturuldu',
                classes: 'green darken-2'
            })

            vzAjax($('#collections'))

            $('#modal-form').modal('close')
        }
    }
@endpush
