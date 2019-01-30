@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Bot Yönetimi',
            'link' => route('crawlers')
        ],
        [
            'text' => '🐞 Twitter Ayarları'
        ]
    ],
    'dock' => true
])

@push('local.scripts')
    var statisticsTimer;

    function __statistics(__, obj)
    {
        if (obj.status == 'ok')
        {
            if (obj.data.twitter.tweets.data._all.primaries.docs)
            {
                $('[data-name=tweet-count]').html(number_format(obj.data.twitter.tweets.data._all.primaries.docs.count))
                $('[data-name=tweet-size]').html(humanFileSize(obj.data.twitter.tweets.data._all.total.store.size_in_bytes))
            }
            else
            {
                $('[data-elasticsearch=tweets]').html('Index Oluşturulmadı!')
            }

            if (obj.data.twitter.trends.message)
            {
                var message = $.parseJSON(obj.data.twitter.trends.message);
            }
            else
            {
                var message = { 'status': obj.data.twitter.trends.status == 'ok' ? 200 : 404 };
            }

            if (message.status == '404')
            {
                $('[data-elasticsearch=trends]').html('Index Oluşturulmadı!')
            }
            else
            {
                $('[data-name=trend-count]').html(number_format(obj.data.twitter.trends.data._all.primaries.docs.count))
                $('[data-name=trend-size]').html(humanFileSize(obj.data.twitter.trends.data._all.total.store.size_in_bytes))
            }

            window.clearTimeout(statisticsTimer)

            statisticsTimer = window.setTimeout(function() {
                vzAjax($('[data-callback=__statistics]'))
            }, 10000)
        }
    }

    var collection_timer;

    function __collections(__, obj)
    {
        var ul = $('#collections');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var selector = $('[data-id=' + o.id + '].collection-item'),

                        item = selector.length ? selector : item_model.clone();

                        item.removeClass('model hide red green orange grey')
                            .addClass('_tmp d-flex')
                            .attr('data-id', o.id)
                        item.addClass(o.status == 'disabled' ? 'red' : (o.pid === false ? 'orange' : (o.pid === null ? 'grey' : 'green')))

                        item.find('[data-name=pid]').val(o.status == 'disabled' ? 'Sorunlu' : (o.pid === false ? 'Yeniden Başlatılacak' : (o.pid === null ? 'Devre Dışı' : o.pid)))
                        item.find('[data-name=tmp_key]').val(o.tmp_key)
                        item.find('[data-name=value]').val(o.value)

                        if (!selector.length)
                        {
                            item.find('[data-name=error]').val(o.error_count + ' / ' + o.off_limit + ' hata')
                            item.appendTo(ul)
                        }
                })
            }

            $('#home-loader').hide()
        }

        window.clearTimeout(collection_timer)

        collection_timer = window.setTimeout(function() {
            vzAjax($('#collections'))
        }, 5000)
    }

    function form_modal()
    {
        var mdl = modal({
            'id': 'token',
            'body': $('<form />', {
                'action': '{{ route('admin.twitter.token') }}',
                'id': 'form',
                'class': 'json',
                'html': [
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<input />', {
                                'id': 'consumer_key',
                                'name': 'consumer_key',
                                'type': 'text',
                                'class': 'validate',
                                'data-length': 255
                            }),
                            $('<label />', {
                                'for': 'consumer_key',
                                'html': 'Consumer Key'
                            }),
                            $('<span />', {
                                'class': 'helper-text'
                            })
                        ]
                    }),
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<input />', {
                                'id': 'consumer_secret',
                                'name': 'consumer_secret',
                                'type': 'text',
                                'class': 'validate',
                                'data-length': 255
                            }),
                            $('<label />', {
                                'for': 'consumer_secret',
                                'html': 'Consumer Secret'
                            }),
                            $('<span />', {
                                'class': 'helper-text'
                            })
                        ]
                    }),
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<input />', {
                                'id': 'access_token',
                                'name': 'access_token',
                                'type': 'text',
                                'class': 'validate',
                                'data-length': 255
                            }),
                            $('<label />', {
                                'for': 'access_token',
                                'html': 'Access Token'
                            }),
                            $('<span />', {
                                'class': 'helper-text'
                            })
                        ]
                    }),
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<input />', {
                                'id': 'access_token_secret',
                                'name': 'access_token_secret',
                                'type': 'text',
                                'class': 'validate',
                                'data-length': 255
                            }),
                            $('<label />', {
                                'for': 'access_token_secret',
                                'html': 'Access Token Secret'
                            }),
                            $('<span />', {
                                'class': 'helper-text'
                            })
                        ]
                    }),
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<input />', {
                                'id': 'off_limit',
                                'name': 'off_limit',
                                'type': 'number',
                                'class': 'validate',
                                'max': 100,
                                'min': 10,
                                'value': 10
                            }),
                            $('<label />', {
                                'for': 'off_limit',
                                'html': 'Kapatma Limiti'
                            }),
                            $('<span />', {
                                'class': 'helper-text',
                                'html': 'Girilen değer kadar hata alındığında token devre dışı kalsın.'
                            })
                        ]
                    })
                ]
            }),
            'size': 'modal-medium',
            'options': {
                dismissible: false
            },
            'footer': [
               $('<a />', {
                   'href': '#',
                   'class': 'modal-close waves-effect btn-flat grey-text',
                   'html': buttons.cancel
               }),
               $('<span />', {
                   'html': ' '
               }),
               $('<a />', {
                   'data-trigger': 'delete',
                   'href': '#',
                   'class': 'waves-effect btn-flat grey-text hide',
                   'html': buttons.remove
               }),
               $('<span />', {
                   'html': ' '
               }),
               $('<button />', {
                   'type': 'submit',
                   'class': 'waves-effect btn-flat cyan-text',
                   'data-submit': 'form#form',
                   'html': buttons.ok
               })
            ]
        });

        return mdl;
    }

    $(document).on('click', '[data-trigger=delete]', function() {
        return modal({
            'id': 'alert',
            'body': 'Token silinecek?',
            'size': 'modal-small',
            'title': 'Sil',
            'options': {},
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect btn-flat grey-text',
                    'html': buttons.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<a />', {
                    'href': '#',
                    'class': 'waves-effect btn-flat red-text json',
                    'html': buttons.ok,
                    'data-href': '{{ route('admin.twitter.token') }}',
                    'data-method': 'delete',
                    'data-id': $(this).data('id'),
                    'data-callback': '__delete'
                })
            ]
        })
    })

    $(document).on('click', '[data-trigger=create]', function() {
        var _modal = form_modal();
            _modal.find('.modal-title').html('Token Oluştur')

        var form = _modal.find('form#form')

        $('input[name=consumer_key]').val('').characterCounter()
        $('input[name=consumer_secret]').val('').characterCounter()
        $('input[name=access_token]').val('').characterCounter()
        $('input[name=access_token_secret]').val('').characterCounter()
        $('input[name=off_limit]').val('10')

        $('[data-trigger=delete]').removeAttr('data-id').addClass('hide')

        form.removeAttr('data-id')
        form.attr('method', 'put')
        form.data('callback', '__create')

        M.updateTextFields()
    })

    function __delete(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('[data-id=' + obj.data.id + '].collection-item').remove()

            $('#modal-alert').modal('close')

            setTimeout(function() {
                $('#modal-token').modal('close')
            }, 200)

            M.toast({
                html: 'Token silindi.',
                classes: 'green darken-2'
            })
        }
        else if (obj.status == 'err')
        {
            M.toast({
                html: 'Çalışan bir token silinemez!',
                classes: 'red'
            })
        }
    }

    function __update(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({
                html: 'Token Güncellendi',
                classes: 'green darken-2'
            })

            $('#modal-token').modal('close')
        }
    }

    function __create(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({
                html: 'Token Oluşturuldu',
                classes: 'green darken-2'
            })

            $('#modal-token').modal('close')
        }
    }

    function __get(__, obj)
    {
        if (obj.status == 'ok')
        {
            var _modal = form_modal();
                _modal.find('.modal-title').html('Token Güncelle')

            var form = _modal.find('form#form')

            $('input[name=consumer_key]').val(obj.data.consumer_key).characterCounter()
            $('input[name=consumer_secret]').val(obj.data.consumer_secret).characterCounter()
            $('input[name=access_token]').val(obj.data.access_token).characterCounter()
            $('input[name=access_token_secret]').val(obj.data.access_token_secret).characterCounter()
            $('input[name=off_limit]').val(obj.data.off_limit)

            $('[data-trigger=delete]').data('id', obj.data.id).removeClass('hide')

            form.data('id', obj.data.id)
            form.attr('method', 'patch')
            form.data('callback', '__update')

            M.updateTextFields()

            if (obj.data.off_reason)
            {
                setTimeout(function() {
                    var _mdl = modal({
                            'id': 'off-reason',
                            'body': [
                                $('<p />', {
                                    'html': 'Bu token aşağıdaki sebepten dolayı devre dışı kaldı.',
                                    'class': 'red-text'
                                }),
                                $('<p />', {
                                    'html': obj.data.off_reason
                                })
                            ],
                            'size': 'modal-medium',
                            'title': 'Kapatma Mesajı',
                            'options': {},
                            'footer': [
                                $('<a />', {
                                    'href': '#',
                                    'class': 'modal-close waves-effect btn-flat cyan-text',
                                    'html': buttons.ok
                                })
                            ]
                        });
                }, 500)
            }
        }
    }

    function __connection_failed(__)
    {
        $('[data-elasticsearch]').html('ES Bağlantı Hatası')
    }
@endpush

@section('action-bar:half')
    <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-trigger="create">
        <i class="material-icons grey-text text-darken-2">add</i>
    </a>
@endsection

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Twitter Ayarları</span>
        </div>
        <div class="card-tabs">
            <ul class="tabs tabs-fixed-width">
                <li class="tab">
                    <a href="#stats" class="active">Token</a>
                </li>
                <li class="tab">
                    <a href="#logs">Log</a>
                </li>
            </ul>
        </div>
        <div id="stats">
            <div class="card-content grey-text">
                <div
                    class="item-group load"
                    data-href="{{ route('admin.twitter.statistics') }}"
                    data-timeout="4000"
                    data-method="post"
                    data-callback="__statistics"
                    data-error-callback="__connection_failed">
                    <div class="item">
                        <small class="d-block grey-text">Alınan Tweet</small>
                        <p data-elasticsearch="tweets" data-name="tweet-count">-</p>
                    </div>
                    <div class="item">
                        <small class="d-block grey-text">Kullanılan Alan</small>
                        <p data-elasticsearch="tweets" data-name="tweet-size">-</p>
                    </div>
                    <div class="item">
                        <small class="d-block grey-text">Alınan Trend Başlık</small>
                        <p data-elasticsearch="trends" data-name="trend-count">-</p>
                    </div>
                    <div class="item">
                        <small class="d-block grey-text">Kullanılan Alan</small>
                        <p data-elasticsearch="trends" data-name="trend-size">-</p>
                    </div>
                </div>
            </div>
            <div class="card-content red hide" data-name="alert"></div>
            <div class="collection load" 
                 id="collections"
                 data-href="{{ route('admin.twitter.tokens.json') }}"
                 data-callback="__collections"
                 data-method="post"
                 data-nothing>
                <div class="collection-item nothing hide">
                    @component('components.nothing')@endcomponent
                </div>
                <a
                    class="collection-item model hide flex-wrap red z-depth-4 waves-effect json"
                    data-href="{{ route('admin.twitter.token') }}"
                    data-method="post"
                    data-callback="__get"
                    href="#">
                    <input data-name="error" readonly type="text" />
                    <input data-name="pid" readonly type="text" class="right-align" />
                    <input data-name="tmp_key" readonly type="text" class="white-text" />
                    <input data-name="value" readonly type="text" class="white-text" />
                </a>
            </div>
        </div>
        <div id="logs" style="display: none;">
            <div class="card-content grey-text">Bu alan sadece "Twitter" modülü ile ilgili logları gösterir.</div>
            <ul
                id="console"
                class="collection black load d-flex align-items-end flex-wrap no-select"
                data-href="{{ route('admin.twitter.monitoring.log') }}"
                data-callback="__log"
                data-method="post">
                <li class="collection-item hide" style="width: 100%;">
                    <p>
                        <span data-name="level"></span>
                        <span class="grey-text text-lighten-2" style="padding: 0 .2rem;">/</span>
                        <span data-name="repeat"></span>
                        <span class="grey-text text-lighten-2" style="padding: 0 .2rem;">/</span>
                        <time data-name="updated-at" class="timeago grey-text text-darken-2"></time>
                    </p>
                    <p>
                        <time data-name="created-at" class="timeago grey-text text-darken-2"></time>
                        <span data-name="module" class="grey-text text-darken-2"></span>
                    </p>
                    <textarea data-name="message" class="green-text d-block"></textarea>
                </li>
            </ul>
        </div>
    </div>

    @component('components.loader')
        @slot('color', 'cyan')
        @slot('id', 'home-loader')
    @endcomponent
@endsection

@push('local.scripts')
    $(document).ready(function() {
        $('.tabs').tabs()
    })

    var logTimer;

    function __log(__, obj)
    {
        if (obj.status == 'ok')
        {
            var collection = $('ul#console');
            var model = collection.children('li.collection-item.hide');

            if (obj.data.length)
            {
                $.each(obj.data, function(key, o) {
                    var m = $('[data-id=' + o.uuid + ']');

                    var item = m.length ? m : model.clone();
                        item.removeClass('hide')
                            .attr('data-id', o.uuid)

                        item.find('[data-name=level]').html(o.level + '. seviye').addClass(o.level <= 4 ? 'green-text' : o.level <= 7 ? 'orange-text' : 'red-text')
                        item.find('[data-name=repeat]').html(o.hit + ' tekrar').addClass(o.hit <= 10 ? 'green-text' : o.hit <= 20 ? 'orange-text' : 'red-text')
                        item.find('[data-name=updated-at]').attr('data-time', o.updated_at)
                        item.find('[data-name=created-at]').attr('data-time', o.created_at)
                        item.find('[data-name=module]').html(o.module)
                        item.find('[data-name=message]').val(o.message)

                    if (m.length)
                    {
                        if (m.attr('data-repeat') != o.hit)
                        {
                            item.attr('data-repeat', o.hit)
                        }
                    }
                    else
                    {
                        item.find('[data-name=updated-at]').html(o.updated_at)
                        item.find('[data-name=created-at]').html(o.created_at)
                        item.attr('data-repeat', o.hit)
                    }

                    item.appendTo(collection)
                })
            }

            window.clearTimeout(logTimer)

            logTimer = window.setTimeout(function() {
                vzAjax($('ul#console'))
            }, 10000)
        }
    }
@endpush

@push('local.styles')
    ul#console {
        height: 400px;
        overflow-y: scroll;
        background-image: url('{{ asset('img/olive-logo-opacity.svg') }}');
        background-repeat: no-repeat;
        background-position: center;
    }

    ul#console > li textarea {
        border-width: 0;
        resize: none;
    }

    #collections {
        overflow: hidden;
    }
    #collections > a.collection-item > input {
        border-width: 0;
        margin: 0;
        padding: 0;
        height: 24px;
        cursor: pointer;
    }
    #collections > a.collection-item > input:nth-of-type(1),
    #collections > a.collection-item > input:nth-of-type(2) {
        width: 50%;
    }
@endpush

@section('dock')
    <div class="card">
        <div class="collection">
            @if ($options['twitter.index.trends'] == 'off')
                <div class="collection-item d-block orange-text">
                    <i class="material-icons d-table">warning</i>
                    Trend indexinin oluşturulması bekleniyor.
                </div>
            @else
                <label class="collection-item waves-effect d-block">
                    <input
                        name="value"
                        id="value"
                        value="on"
                        class="json"
                        data-href="{{ route('admin.twitter.option.set') }}"
                        data-method="patch"
                        data-delay="1"
                        data-key="twitter.trend.status"
                        data-checked-value="on"
                        data-unchecked-value="off"
                        type="checkbox"
                        @if ($options['twitter.trend.status'] == 'on'){{ 'checked' }}@endif  />
                    <span>Trend Botu</span>
                </label>
            @endif
            @if ($options['twitter.index.tweets'] == date('Y.m', strtotime('+ 1 month')))
                <label class="collection-item waves-effect d-block">
                    <input
                        name="value"
                        id="value"
                        value="on"
                        class="json"
                        data-href="{{ route('admin.twitter.option.set') }}"
                        data-method="patch"
                        data-delay="1"
                        data-key="twitter.status"
                        data-checked-value="on"
                        data-unchecked-value="off"
                        type="checkbox"
                        @if ($options['twitter.status'] == 'on'){{ 'checked' }}@endif  />
                    <span>Tweet Botu</span>
                </label>
            @else
                <div class="collection-item d-block orange-text">
                    <i class="material-icons d-table">warning</i>
                    Tweet indexlerinin oluşturulması bekleniyor.
                </div>
            @endif
        </div>
    </div>
    @include('crawlers.twitter._menu', [ 'active' => 'dashboard' ])
@endsection
