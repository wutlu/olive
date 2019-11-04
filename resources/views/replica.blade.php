@extends('layouts.app', [
    'sidenav_layout' => true,
    'footer_hide' => true,
    'breadcrumb' => [
        [
            'text' => 'Kopya İçerik Tespiti (beta)'
        ]
    ],
    'wide' => true
])

@push('local.styles')
    .marked {
        padding: .4rem;
        border-radius: .2rem;
    }

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
        $('input[name=string]').val('').effect( 'highlight', { 'color': '#e8f5e9' }, 800 ).focus()
    }).on('keyup', 'input[name=string]', function(e) {
        var __ = $(this),
            keycode = (e.keyCode ? e.keyCode : e.which);

        if (keycode == '13')
        {
            var search = $('ul#search');
                search.data('skip', 0).addClass('json-clear');

            vzAjax(search)
        }
    })

    $('select').formSelect()
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/jquery.ui.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/speakingurl.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
    <script src="{{ asset('js/jquery.mark.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
    <script src="{{ asset('js/jquery.table2excel.min.js?v='.config('system.version')) }}"></script>
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
            class="json-search"
            data-json-target="ul#search" />
    </div>

    <div class="d-flex justify-content-between" id="search-tools">
        <div class="d-flex justify-content-start flex-wrap">
            <div class="input-field m-0 align-self-center">
                <select name="smilarity" id="smilarity">
                    <option value="100" selected>100%</option>
                    <option value="90">90%</option>
                    <option value="80">80%</option>
                    <option value="70">70%</option>
                    <option value="60">60%</option>
                    <option value="50">50%</option>
                    <option value="40">40%</option>
                </select>
                <small class="helper-text">Benzerlik Oranı</small>
            </div>
            <div class="input-field m-0 align-self-center">
                <select name="source" id="source">
                    <option value="news">Haber</option>
                </select>
                <small class="helper-text">Veri Türü</small>
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

    <div class="pl-1 pr-1 grey-text mt-1" data-name="stats"></div>
@endsection

@section('content')
    <div class="row">
        <div class="col s12 l6 xl7">
            <div class="card card-unstyled">
                <ul class="collection collection-unstyled json-clear loading" 
                    id="search"
                    data-href="{{ route('replica.dashboard') }}"
                    data-skip="0"
                    data-take="50"
                    data-more-button="#search-more_button"
                    data-callback="__search"
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
                    <li class="collection-item model hide">
                        <div class="p-1">
                            <h5 class="m-0 d-table blue-text" data-name="title"></h5>
                            <span class="grey-text" data-name="created_at"></span>
                            <p class="mb-0 grey-text text-darken-2" data-name="description"></p>
                            <a href="#" class="d-table green-text" data-name="url"></a>
                        </div>
                    </li>
                </ul>
            </div>
            <a href="#"
               class="more hide json"
               id="search-more_button"
               data-json-target="ul#search">Daha Fazla</a>
        </div>
        <div class="col s12 l6 xl5" id="stats"></div>
    </div>
@endsection

@push('local.scripts')
    function __search(__, obj)
    {
        var item_model = __.children('.model');

        if (obj.status == 'ok')
        {
            $('[data-name=stats]').html('Yaklaşık ' + number_format(obj.stats.hits) + ' sonuç bulundu (' + obj.stats.took + ' saniye)').removeClass('hide');

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var selector = __.children('[data-id=' + o.id + ']');

                        item = selector.length ? selector : item_model.clone();

                        item.removeClass('model hide').addClass('_tmp d-flex').attr('data-id', o.id)

                        item.find('[data-name=title]').html(o.title)
                        item.find('[data-name=description]').html(o.description)
                        item.find('[data-name=created_at]').html(o.created_at)
                        item.find('[data-name=url]').html(o.url).attr('href', o.url)

                        item.mark(obj.words, {
                            'element': 'span',
                            'className': 'marked yellow black-text',
                            'accuracy': 'complementary'
                        })

                        item.appendTo(__)
                })
            }

            if (obj.aggs.unique)
            {
                var chart = $('#unique-chart');

                if (chart.length)
                {
                    chart.remove()
                }

                var chart = $('<div />', {
                    'id': 'unique-chart',
                    'class': 'card mb-2',
                    'html': $('<div />', {
                        'class': 'card-content',
                        'html': [
                            $('<span />', {
                                'html': 'Tekil Site: ' + '(' + obj.aggs.unique.length + ')',
                                'class': 'card-title'
                            }),
                            $('<table />', {
                                'id': 'unique-sites',
                                'class': 'highlight',
                                'html': [
                                    $('<thead />', {
                                        'html': $('<tr />', {
                                            'html': [
                                                $('<th />', { 'html': 'Site Adı' }),
                                                $('<th />', { 'html': 'Site Adresi' }),
                                                $('<th />', { 'html': 'İçerik Sayısı' })
                                            ]
                                        })
                                    }),
                                    $('<tbody />'),
                                    $('<tfoot />', {
                                        'html': $('<tr />', {
                                            'html': $('<td />', {
                                                'colspan': 3,
                                                'class': 'center-align',
                                                'html': $('<a />', {
                                                    'class': 'btn-flat waves-effect noExl',
                                                    'href': '#',
                                                    'data-excel': '#unique-sites',
                                                    'data-name': 'Excel Kopya',
                                                    'html': 'Excel\'e Aktar'
                                                })
                                            })
                                        })
                                    })
                                ]
                            })
                        ]
                    })
                })

                $.each(obj.aggs.unique, function(key, item) {
                    var address = item.address;

                    if (item.base != '/')
                    {
                        address = address + '/' + item.base;
                    }

                    var tr = $('<tr />', {
                        'html': [
                            $('<td />', { 'html': item.name, 'css': { 'padding': '4px' } }),
                            $('<td />', {
                                'html': $('<a />', {
                                    'class': 'green-text',
                                    'href': address,
                                    'html': item.address,
                                    'target': '_blank'
                                }),
                                'css': {
                                    'padding': '4px'
                                }
                            }),
                            $('<td />', { 'html': item.hit, 'css': { 'padding': '4px' } })
                        ]
                    })

                    chart.find('#unique-sites').children('tbody').append(tr)
                })

                chart.prependTo('#stats')
            }

            if (obj.aggs.locals)
            {
                var chart = $('#local_press-chart');

                if (chart.length)
                {
                    chart.remove()
                }

                var chart = $('<div />', {
                    'id': 'local_press-chart',
                    'class': 'mb-2',
                    'html': [
                        $('<h6 />', {
                            'html': 'Yerel Basın: ' + '(' + obj.aggs.locals.length + ')',
                            'class': 'grey-text m-0'
                        }),
                        $('<div />', {
                            'class': 'tr-map'
                        })
                    ]
                })

                var total = 0;

                $.each(obj.aggs.locals, function(key, o) {
                    total = total + o.doc_count;
                })

                $.each(obj.aggs.locals, function(key, o) {
                    var per = parseInt(o.doc_count*255)/total;
                    var cr = per,
                        cg = 0,
                        cb = 0,
                        color = 'rgba(' + cr + ', ' + cg + ', ' + cb + ')';

                    chart.children('.tr-map').append($('<small />', {
                        'class': 'state state-' + getSlug(o.key),
                        'data-title': o.key,
                        'html': o.doc_count,
                        'css': { 'background-color': color }
                    }))
                })

                chart.prependTo('#stats')
            }
        }
    }
@endpush
