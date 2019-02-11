@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Arama Motoru'
        ]
    ],
    'dock' => true
])

@push('local.styles')
    .marked {
        padding: .4rem;
        border-radius: .2rem;
    }

    .time-line > .collection > .collection-item {
        word-break: break-all;
    }
@endpush

@push('local.scripts')
    var group_select = $('select[name=group_id]');
        group_select.formSelect()

    function __pin(__, obj)
    {
        var pins_button = $('[data-button=pins-button]');
        var pin_count = pins_button.children('span.count');

        if (obj.status == 'removed')
        {
            $('[data-pin-uuid=' + __.attr('data-pin-uuid') + ']').removeClass('on')

            M.toast({ html: 'Pin Kaldırıldı', classes: 'red darken-2' })

            pin_count.html(parseInt(pin_count.html()) - 1)
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

            pin_count.html(parseInt(pin_count.html()) + 1)
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
            $('[data-button=pins-button]').removeAttr('disabled').attr('data-id', obj.data.id).children('span.count').html(obj.data.pins.length)
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
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp').attr('data-id', 'list-item-' + o.id)

                        if  (o._type == 'tweet')
                        {
                            var model = $('<div />', {
                                'html': [
                                    $('<div />', {
                                        'html': [
                                            $('<time>', {
                                                'html': o.created_at,
                                                'class': 'd-table grey-text text-lighten-1'
                                            }),
                                            $('<a />', {
                                                'html': 'https://twitter.com/' + o.user.screen_name + '/status/' + o._id,
                                                'href': 'https://twitter.com/' + o.user.screen_name + '/status/' + o._id,
                                                'class': 'd-table green-text'
                                            }).attr('target', '_blank'),
                                            $('<a />', {
                                                'html': o.user.name,
                                                'href': 'https://twitter.com/' + o.user.screen_name,
                                                'class': 'd-table red-text'
                                            }).attr('target', 'blank'),
                                            $('<span />', {
                                                'html': o.text,
                                                'class': 'black-text'
                                            })
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
                                            $('<time>', {
                                                'html': o.created_at,
                                                'class': 'd-table grey-text text-lighten-1'
                                            }),
                                            $('<a />', {
                                                'html': o.url,
                                                'href': o.url,
                                                'class': 'd-table green-text'
                                            }).attr('target', '_blank'),
                                            $('<span />', {
                                                'html': o.title,
                                                'class': 'd-table teal-text'
                                            }),
                                            $('<span />', {
                                                'html': o.author,
                                                'class': 'd-table red-text'
                                            }),
                                            $('<span />', {
                                                'html': o.text,
                                                'class': 'black-text'
                                            })
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
                                            $('<time>', {
                                                'html': o.created_at,
                                                'class': 'd-table grey-text text-lighten-1'
                                            }),
                                            $('<a />', {
                                                'html': str_limit(o.url, 96),
                                                'href': o.url,
                                                'class': 'd-table green-text'
                                            }).attr('target', '_blank'),
                                            $('<span />', {
                                                'html': o.title,
                                                'class': 'd-table teal-text'
                                            }),
                                            $('<span />', {
                                                'html': o.text,
                                                'class': 'black-text'
                                            })
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
                                            $('<time>', {
                                                'html': o.created_at,
                                                'class': 'd-table grey-text text-lighten-1'
                                            }),
                                            $('<a />', {
                                                'html': str_limit(o.url, 96),
                                                'href': o.url,
                                                'class': 'd-table green-text'
                                            }).attr('target', '_blank'),
                                            $('<span />', {
                                                'html': o.title,
                                                'class': 'd-table teal-text'
                                            }),
                                            $('<span />', {
                                                'html': o.text ? o.text : 'Açıklama Yok',
                                                'class': 'black-text'
                                            })
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
                                            $('<time>', {
                                                'html': o.created_at,
                                                'class': 'd-table grey-text text-lighten-1'
                                            }),
                                            $('<a />', {
                                                'html': o.channel.title,
                                                'href': 'https://www.youtube.com/channel/' + o.channel.id,
                                                'class': 'd-table teal-text'
                                            }).attr('target', '_blank'),
                                            $('<a />', {
                                                'html': 'https://www.youtube.com/watch?v=' + o.video_id,
                                                'href': 'https://www.youtube.com/watch?v=' + o.video_id,
                                                'class': 'd-table green-text'
                                            }).attr('target', '_blank'),
                                            $('<span />', {
                                                'html': o.text,
                                                'class': 'black-text'
                                            })
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
                                            $('<time>', {
                                                'html': o.created_at,
                                                'class': 'd-table grey-text text-lighten-1'
                                            }),
                                            $('<a />', {
                                                'html': o.channel.title,
                                                'href': 'https://www.youtube.com/channel/' + o.channel.id,
                                                'class': 'd-table teal-text'
                                            }).attr('target', '_blank'),
                                            $('<a />', {
                                                'html': o.title,
                                                'href': 'https://www.youtube.com/watch?v=' + o._id,
                                                'class': 'd-table green-text'
                                            }).attr('target', '_blank'),
                                            $('<span />', {
                                                'html': o.text,
                                                'class': 'black-text'
                                            })
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
                                    'class': 'btn-small waves-effect white grey-text text-darken-2',
                                    'href': '{{ url('/') }}/db/' + o._index + '/' + o._type + '/' + o._id,
                                    'html': $('<i />', {
                                        'class': 'material-icons',
                                        'html': 'info'
                                    })
                                }),
                                $('<span />', { 'html': ' ' }),
                                $('<a />', {
                                    'href': '#',
                                    'html': $('<i />', {
                                        'class': 'material-icons',
                                        'html': 'add'
                                    }),
                                    'class': 'btn-small waves-effect white grey-text text-darken-2 json',
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
            }

            $('#home-loader').hide()
        }
    }
@endpush

@section('content')
    <div class="card card-unstyled">
        <div class="card-content d-flex justify-content-between">
            <span class="card-title">Arama Motoru</span>
            <div class="d-flex justify-content-end mb-1">
                <a
                    class="btn white grey-text text-darken-2 waves-effect json"
                    disabled
                    data-button="pins-button"
                    data-href="{{ route('route.generate.id') }}"
                    data-method="post"
                    data-name="pin.pins"
                    data-callback="__go"
                    href="#">Pinler (<span class="count">0</span>)</a>
                <a href="#" class="btn white waves-effect ml-1" data-trigger="info">
                    <i class="material-icons tiny grey-text">info_outline</i>
                </a>
            </div>
        </div>
        <nav class="nav-half mb-0 gree">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search white"
                           data-json-target="#search"
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                    <i class="material-icons">close</i>
                </div>
            </div>
        </nav>
        <div class="time-line">
            <ul class="collection json-clear" 
                id="search"
                data-href="{{ route('search.request') }}"
                data-skip="0"
                data-take="10"
                data-more-button="#search-more_button"
                data-callback="__search_archive"
                data-method="post"
                data-include="start_date,end_date,sentiment,modules,string"
                data-nothing>
                <li class="collection-item nothing hide">
                    @component('components.nothing')
                        @slot('size', 'small')
                        @slot('cloud_class', 'white-text')
                        @slot('text', 'Sonuç bulunamadı!')
                        @slot('text_class', 'grey-text')
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
            @foreach (config('system.modules') as $key => $module)
                <label class="collection-item waves-effect d-block">
                    <input name="modules" checked value="{{ $key }}" type="checkbox" />
                    <span>{{ $module }}</span>
                </label>
            @endforeach
        </div>
    </div>

    @include('pin.group.dock')
@endsection

@push('local.scripts')
    $('.datepicker').datepicker({
        firstDay: 0,
        format: 'yyyy-mm-dd',
        i18n: date.i18n,
        container: 'body'
    })
@endpush
