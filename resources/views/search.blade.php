@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Arama Motoru'
        ]
    ],
    'dock' => true,
    'wide' => true
])

@push('local.styles')
    .marked {
        padding: .4rem;
        border-radius: .2rem;
    }

    .time-line > .collection > .collection-item {
        word-break: break-word;
        padding: 2rem;

    }
    .time-line > .collection > .collection-item .title {
        font-size: 16px;
    }

    .search-field {
        padding: 1rem 0;
    }

    .search-field #string {
                transition: all 400ms cubic-bezier(0.25, 0.46, 0.45, 0.94);
        -webkit-transition: all 400ms cubic-bezier(0.25, 0.46, 0.45, 0.94);

        margin: 0;
        padding: 0 1rem;
        border-width: 0;

                box-shadow: .1rem .1rem .2rem 0 rgba(0, 0, 0, .1);
        -webkit-box-shadow: .1rem .1rem .2rem 0 rgba(0, 0, 0, .1);

        width: calc(100% - 2rem);
        max-width: 512px;
    }
@endpush

@push('local.scripts')
    var group_select = $('select[name=group_id]');
        group_select.formSelect()

    function __pin(__, obj)
    {
        if (obj.status == 'removed')
        {
            $('[data-pin-uuid=' + __.attr('data-pin-uuid') + ']').removeClass('on')

            M.toast({ html: 'Pin Kaldırıldı', classes: 'red darken-2' })
        }
        else if (obj.status == 'pinned')
        {
            $('[data-pin-uuid=' + __.attr('data-pin-uuid') + ']').addClass('on')

            var toastHTML = $('<div />', {
                'html': [
                    $('<span />', {
                        'html': 'İçerik Pinlendi',
                        'class': 'white-text'
                    }),
                    $('<a />', {
                        'href': '#',
                        'class': 'btn-flat toast-action json',
                        'html': 'Geri Al',
                        'data-undo': 'true',
                        'data-href': '{{ route('pin', 'remove') }}',
                        'data-method': 'post',
                        'data-callback': '__pin',
                        'data-id': __.data('id'),
                        'data-type': __.data('type'),
                        'data-index': __.data('index'),
                        'data-pin-uuid': __.data('pin-uuid'),
                        'data-include': 'group_id'
                    })
                ]
            });

            M.toast({ html: toastHTML.get(0).outerHTML })
        }
        else if (obj.status == 'failed')
        {
            M.toast({ html: 'Hay aksi, beklenmedik bir durum.', classes: 'orange darken-2' })
        }
    }

    function __pin_group(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'Grup Seçildi' })
        }
    }

    function __search_archive(__, obj)
    {
        var ul = $('#search');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $('[data-name=stats]').html('Yaklaşık ' + obj.stats.hits + ' sonuç bulundu (' + obj.stats.took + ' saniye) ');

                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp').attr('data-id', 'list-item-' + o.id)

                        if  (o._type == 'tweet')
                        {
                            var model = $('<div />', {
                                'html': [
                                    $('<div />', {
                                        'html': [
                                            $('<a />', {
                                                'html': o.user.name,
                                                'href': 'https://twitter.com/' + o.user.screen_name,
                                                'class': 'd-table red-text'
                                            }).attr('target', 'blank'),
                                            $('<time>', {
                                                'html': o.created_at,
                                                'class': 'd-table grey-text text-lighten-1'
                                            }),
                                            $('<span />', {
                                                'html': o.text,
                                                'class': 'grey-text text-darken-2'
                                            }),
                                            $('<a />', {
                                                'html': 'https://twitter.com/' + o.user.screen_name + '/status/' + o._id,
                                                'href': 'https://twitter.com/' + o.user.screen_name + '/status/' + o._id,
                                                'class': 'd-table green-text'
                                            }).attr('target', '_blank')
                                        ]
                                    })
                                ]
                            }).mark(obj.words, {
                                'element': 'span',
                                'className': 'marked yellow black-text',
                                'accuracy': 'complementary'
                            });

                            if (o.deleted_at)
                            {
                                model.css({ 'opacity': '.4' })
                            }
                        }
                        else if  (o._type == 'entry')
                        {
                            var model = $('<div />', {
                                'html': [
                                    $('<div />', {
                                        'html': [
                                            $('<span />', {
                                                'html': o.title,
                                                'class': 'd-table blue-text title'
                                            }),
                                            $('<span />', {
                                                'html': o.author,
                                                'class': 'd-table red-text'
                                            }),
                                            $('<time>', {
                                                'html': o.created_at,
                                                'class': 'd-table grey-text text-lighten-1'
                                            }),
                                            $('<span />', {
                                                'html': o.text,
                                                'class': 'grey-text text-darken-2'
                                            }),
                                            $('<a />', {
                                                'html': o.url,
                                                'href': o.url,
                                                'class': 'd-table green-text'
                                            }).attr('target', '_blank')
                                        ]
                                    })
                                ]
                            }).mark(obj.words, {
                                'element': 'span',
                                'className': 'marked yellow black-text',
                                'accuracy': 'complementary'
                            });

                            if (o.deleted_at)
                            {
                                model.css({ 'opacity': '.4' })
                            }
                        }
                        else if (o._type == 'article')
                        {
                            var model = $('<div />', {
                                'html': [
                                    $('<div />', {
                                        'html': [
                                            $('<span />', {
                                                'html': o.title,
                                                'class': 'd-table blue-text title'
                                            }),
                                            $('<time>', {
                                                'html': o.created_at,
                                                'class': 'd-table grey-text text-lighten-1'
                                            }),
                                            $('<span />', {
                                                'html': o.text,
                                                'class': 'grey-text text-darken-2'
                                            }),
                                            $('<a />', {
                                                'html': str_limit(o.url, 96),
                                                'href': o.url,
                                                'class': 'd-table green-text'
                                            }).attr('target', '_blank')
                                        ]
                                    })
                                ]
                            }).mark(obj.words, {
                                'element': 'span',
                                'className': 'marked yellow black-text',
                                'accuracy': 'complementary'
                            });

                            if (o.deleted_at)
                            {
                                model.css({ 'opacity': '.4' })
                            }
                        }
                        else if (o._type == 'product')
                        {
                            var model = $('<div />', {
                                'html': [
                                    $('<div />', {
                                        'html': [
                                            $('<span />', {
                                                'html': o.title,
                                                'class': 'd-table blue-text title'
                                            }),
                                            $('<time>', {
                                                'html': o.created_at,
                                                'class': 'd-table grey-text text-lighten-1'
                                            }),
                                            $('<span />', {
                                                'html': o.text ? o.text : 'Açıklama Yok',
                                                'class': 'grey-text text-darken-2'
                                            }),
                                            $('<a />', {
                                                'html': str_limit(o.url, 96),
                                                'href': o.url,
                                                'class': 'd-table green-text'
                                            }).attr('target', '_blank')
                                        ]
                                    })
                                ]
                            }).mark(obj.words, {
                                'element': 'span',
                                'className': 'marked yellow black-text',
                                'accuracy': 'complementary'
                            });

                            if (o.deleted_at)
                            {
                                model.css({ 'opacity': '.4' })
                            }
                        }
                        else if (o._type == 'comment')
                        {
                            var model = $('<div />', {
                                'html': [
                                    $('<div />', {
                                        'html': [
                                            $('<a />', {
                                                'html': o.channel.title,
                                                'href': 'https://www.youtube.com/channel/' + o.channel.id,
                                                'class': 'd-table red-text'
                                            }).attr('target', '_blank'),
                                            $('<time>', {
                                                'html': o.created_at,
                                                'class': 'd-table grey-text text-lighten-1'
                                            }),
                                            $('<span />', {
                                                'html': o.text,
                                                'class': 'grey-text text-darken-2'
                                            }),
                                            $('<a />', {
                                                'html': 'https://www.youtube.com/watch?v=' + o.video_id,
                                                'href': 'https://www.youtube.com/watch?v=' + o.video_id,
                                                'class': 'd-table green-text'
                                            }).attr('target', '_blank')
                                        ]
                                    })
                                ]
                            }).mark(obj.words, {
                                'element': 'span',
                                'className': 'marked yellow black-text',
                                'accuracy': 'complementary'
                            });

                            if (o.deleted_at)
                            {
                                model.css({ 'opacity': '.4' })
                            }
                        }
                        else if (o._type == 'video')
                        {
                            var model = $('<div />', {
                                'html': [
                                    $('<div />', {
                                        'html': [
                                            $('<span />', {
                                                'html': o.title,
                                                'class': 'd-table blue-text title'
                                            }),
                                            $('<a />', {
                                                'html': o.channel.title,
                                                'href': 'https://www.youtube.com/channel/' + o.channel.id,
                                                'class': 'd-table red-text'
                                            }).attr('target', '_blank'),
                                            $('<time>', {
                                                'html': o.created_at,
                                                'class': 'd-table grey-text text-lighten-1'
                                            }),
                                            $('<span />', {
                                                'html': o.text,
                                                'class': 'grey-text text-darken-2'
                                            }),
                                            $('<a />', {
                                                'html': 'https://www.youtube.com/watch?v=' + o._id,
                                                'href': 'https://www.youtube.com/watch?v=' + o._id,
                                                'class': 'd-table green-text'
                                            }).attr('target', '_blank')
                                        ]
                                    })
                                ]
                            }).mark(obj.words, {
                                'element': 'span',
                                'className': 'marked yellow black-text',
                                'accuracy': 'complementary'
                            });

                            if (o.deleted_at)
                            {
                                model.css({ 'opacity': '.4' })
                            }
                        }

                        $('<div />', {
                            'class': 'mt-1',
                            'html': [
                                $('<a />', {
                                    'class': 'btn-floating btn-small waves-effect white',
                                    'href': '{{ url('/') }}/db/' + o._index + '/' + o._type + '/' + o._id,
                                    'html': $('<i />', {
                                        'class': 'material-icons grey-text text-darken-2',
                                        'html': 'info'
                                    })
                                }),
                                $('<span />', { 'html': ' ' }),
                                $('<a />', {
                                    'href': '#',
                                    'html': $('<i />', {
                                        'class': 'material-icons grey-text text-darken-2',
                                        'html': 'add'
                                    }),
                                    'class': 'btn-floating btn-small waves-effect white json',
                                    'data-href': '{{ route('pin', 'add') }}',
                                    'data-method': 'post',
                                    'data-include': 'group_id',
                                    'data-callback': '__pin',
                                    'data-trigger': 'pin',
                                    'data-id': o._id,
                                    'data-pin-uuid': o.uuid,
                                    'data-index': o._index,
                                    'data-type': o._type
                                })
                            ]
                        }).appendTo(model)

                        item.html(model)
                        item.appendTo(ul)
                })

                $('.dropdown-trigger').dropdown({
                    alignment: 'right'
                })
            }
        }
    }

    @if ($q)
        vzAjax($('#search'))
    @endif

    function __aggregation(__, obj)
    {
        if (obj.status == 'ok')
        {
            alert(1)
        }
    }

    $('.datepicker').datepicker({
        firstDay: 0,
        format: 'dd.mm.yyyy',
        i18n: date.i18n,
        container: 'body'
    })

    $('.owl-wildcard').owlCarousel({
        responsiveClass: true,
        responsive: {
            0: {
                items: 1
            },
            600: {
                items: 2
            },
            1440: {
                items: 3
            }
        },
        autoWidth: true,
        dotClass: 'hide'
    })

    $('.owl-wildcard').trigger('add.owl.carousel', [$('<div />', {
        'html': $('<div />', {
            'html': [
                $('<span />', {
                    'class': 'grey-text',
                    'html': 'Saatlik İçerik Grafiği'
                }),
                $('<div />', {
                    'class': 'owl-chart',
                    'html': $('<canvas />', {
                        'id': 'hourly-chart',
                        'height': '100'
                    })
                })
            ]
        })
    }), 0]).trigger('refresh.owl.carousel')

    function __chart(parent, data)
    {
        var chart = $('<canvas />', {
            'width': '64',
            'height': '24',
            'data-name': 'chart'
        })

        parent.html(chart)

        setTimeout(function() {
            new Chart(chart, {
                type: 'line',
                data: data,
                options: options
            })
        }, 100)
    }
@endpush

@push('local.styles')
    .owl-chart {
        width: 100%;
        height: 100px;
    }

    .owl-chart > #hourly-chart {
        background: #f00;
        width: 100%;
    }
@endpush

@section('content')
    <div class="grey-text mb-1" data-name="stats"></div>
    <div class="card">
        <div class="time-line">
            <ul class="collection json-clear" 
                id="search"
                data-href="{{ route('search.dashboard') }}"
                data-skip="0"
                data-take="100"
                data-more-button="#search-more_button"
                data-callback="__search_archive"
                data-method="post"
                data-include="start_date,end_date,sentiment,modules,string,sort"
                data-nothing>
                <li class="collection-item nothing hide">
                    @component('components.nothing')
                        @slot('size', 'small')
                        @slot('text', 'Sonuç bulunamadı!')
                    @endcomponent
                </li>
                <li class="collection-item model hide"></li>
            </ul>
        </div>
    </div>

    <div class="center-align">
        <button class="btn-flat waves-effect hide json"
                id="search-more_button"
                type="button"
                data-json-target="ul#search">Daha Fazla</button>
    </div>
@endsection

@include('_inc.alerts.search_operators')

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css?v='.config('system.version')) }}" />
@endpush
@push('external.include.footer')
    <script src="{{ asset('js/jquery.mark.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
    <script src="{{ asset('js/owl.carousel.min.js?v='.config('system.version')) }}"></script>
@endpush

@section('wildcard')
    <div class="container container-wide">
        <div class="wild-area">
            <div class="wild-content d-flex" data-wild="date">
                <span class="wild-body d-flex">
                    <a href="#" class="btn-floating btn-flat btn-small waves-effect align-self-center mr-1" data-class=".wild-content" data-class-remove="active">
                        <i class="material-icons">close</i>
                    </a>
                    <input style="max-width: 96px;" type="text" class="datepicker" name="start_date" value="{{ $s ? $s : date('d.m.Y', strtotime('-1 day')) }}" placeholder="Başlangıç" />
                    <input style="max-width: 96px;" type="text" class="datepicker" name="end_date" value="{{ $e ? $e : date('d.m.Y') }}" placeholder="Bitiş" />
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
                    <label class="align-self-center mr-1" data-tooltip="Nötr">
                        <input type="radio" name="sentiment" value="neu" />
                        <span class="material-icons grey-text text-darken-2">sentiment_neutral</span>
                    </label>
                    <label class="align-self-center mr-1" data-tooltip="Negatif">
                        <input type="radio" name="sentiment" value="neg" />
                        <span class="material-icons grey-text text-darken-2">sentiment_dissatisfied</span>
                    </label>
                    <label class="align-self-center mr-1" data-tooltip="Tümü">
                        <input type="radio" name="sentiment" value="all" checked="" />
                        <span class="material-icons grey-text text-darken-2">fullscreen</span>
                    </label>
                </span>
            </div>
            <div class="wild-content d-flex" data-wild="graph">
                <span class="wild-body d-flex">
                    <a href="#" class="btn-floating btn-flat btn-small waves-effect align-self-center mr-1" data-class=".wild-content" data-class-remove="active">
                        <i class="material-icons">close</i>
                    </a>
                    <a href="#" data-type="hourly" data-tooltip="Saatlik İçerik Grafiği" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string" data-href="{{ route('search.aggregation') }}" data-method="post" class="json waves-effect align-self-center mr-1">Saatlik</a>
                    <a href="#" data-type="weekly" data-tooltip="Günlük İçerik Grafiği" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string" data-href="{{ route('search.aggregation') }}" data-method="post" class="json waves-effect align-self-center mr-1">Günlük</a>
                    <a href="#" data-type="location" data-tooltip="Konum Grafiği" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string" data-href="{{ route('search.aggregation') }}" data-method="post" class="json waves-effect align-self-center mr-1">Konum</a>
                    <a href="#" data-type="platform" data-tooltip="Platform Grafiği" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string" data-href="{{ route('search.aggregation') }}" data-method="post" class="json waves-effect align-self-center mr-1">Platform</a>
                    <a href="#" data-type="source" data-tooltip="Kaynak Grafiği" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string" data-href="{{ route('search.aggregation') }}" data-method="post" class="json waves-effect align-self-center mr-1">Kaynak</a>
                    <a href="#" data-type="mention" data-tooltip="Kimler Bahsetti?" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string" data-href="{{ route('search.aggregation') }}" data-method="post" class="json waves-effect align-self-center mr-1">@</a>
                    <a href="#" data-type="hashtag" data-tooltip="Hangi Hashtagler Kullanıldı?" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string" data-href="{{ route('search.aggregation') }}" data-method="post" class="json waves-effect align-self-center mr-1">#</a>

                    <span class="teal-text align-self-center">Bu bölüm kaynak alanından bağımsız çalışır.</span>
                </span>
            </div>
            <div class="wild-content d-flex" data-wild="settings">
                <span class="wild-body d-flex">
                    <a href="#" class="btn-floating btn-flat btn-small waves-effect align-self-center mr-1" data-class=".wild-content" data-class-remove="active">
                        <i class="material-icons">close</i>
                    </a>
                    <label class="align-self-center mr-1">
                        <input type="checkbox" name="sort" value="asc" />
                        <span class="grey-text text-darken-2">İlk İçerikler</span>
                    </label>
                </span>
            </div>
            <ul class="wild-menu">
                <li>
                    <a class="d-flex" href="#" data-class="[data-wild=date]" data-class-add="active">
                        <i class="material-icons">date_range</i>
                    </a>
                </li>
                <li>
                    <a class="d-flex" href="#" data-class="[data-wild=sentiment]" data-class-add="active">
                        <i class="material-icons">mood</i>
                    </a>
                </li>
                <li>
                    <a class="d-flex" href="#" data-class="[data-wild=graph]" data-class-add="active">
                        <i class="material-icons">show_chart</i>
                    </a>
                </li>
                <li>
                    <a class="d-flex" href="#" data-class="[data-wild=settings]" data-class-add="active">
                        <i class="material-icons">settings</i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="z-depth-1">
        <div class="search-field indigo lighten-5">
            <div class="container container-wide">
                <div class="d-flex">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search white mr-1"
                           data-json-target="#search"
                           placeholder="Ara"
                           value="{{ $q }}" />

                    <a href="#" class="align-self-center mr-1" data-trigger="info">
                        <i class="material-icons grey-text text-darken-2">info_outline</i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="z-depth-1">
        <div class="container container-wide">
            <div class="pt-1 pb-1">
                <div class="owl-carousel owl-wildcard"></div>
            </div>
        </div>
    </div>
@endsection

@section('dock')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Kaynak</span>
        </div>
        <div class="collection collection-bordered">
            @foreach (config('system.modules') as $key => $module)
                <label class="collection-item waves-effect d-block">
                    <input name="modules" checked value="{{ $key }}" data-multiple="true" type="checkbox" />
                    <span>{{ $module }}</span>
                </label>
            @endforeach
        </div>
    </div>

    @include('pin.group.dock')
@endsection
