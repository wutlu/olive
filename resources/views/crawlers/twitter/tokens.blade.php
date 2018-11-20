@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Bot Yönetimi'
        ],
        [
            'text' => 'Twitter Ayarları',
            'link' => route('admin.twitter.settings')
        ],
        [
        	'text' => 'Token Yönetimi'
        ]
    ],
    'dock' => true
])

@push('local.scripts')
    var collection_timer;

    function __collections(__, obj)
    {
        var ul = $('#collections');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('d-none')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var selector = $('[data-id=' + o.id + '].collection-item'),

                        item = selector.length ? selector : item_model.clone();

                        item.removeClass('model d-none red green orange grey')
                            .addClass('_tmp d-flex')
                            .attr('data-id', o.id)
                        item.addClass(o.status == 'disabled' ? 'red' : (o.pid === false ? 'orange' : (o.pid === null ? 'grey' : 'green')))

                        item.find('[data-name=pid]').val(o.status == 'disabled' ? 'Sorunlu' : (o.pid === false ? 'Görev Tamamlandı' : (o.pid === null ? 'Devre Dışı' : o.pid)))
                        item.find('[data-name=sh]').val(o.sh)
                        item.find('[data-name=value]').val(o.value)

                        if (!selector.length)
                        {
                            item.find('[data-name=id]').val(o.id)
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
                    }),
                    $('<br />'),
                    $('<div />', {
                        'class': 'right-align',
                        'html': [
                           $('<a />', {
                               'href': '#',
                               'class': 'modal-close waves-effect btn-flat',
                               'html': buttons.cancel
                           }),
                           $('<span />', {
                               'html': ' '
                           }),
                           $('<a />', {
                               'data-trigger': 'delete',
                               'href': '#',
                               'class': 'waves-effect btn-flat red-text d-none',
                               'html': buttons.remove
                           }),
                           $('<span />', {
                               'html': ' '
                           }),
                           $('<button />', {
                               'type': 'submit',
                               'class': 'waves-effect btn',
                               'data-submit': 'form#form',
                               'html': buttons.ok
                           })
                        ]
                    })
                ]
            }),
            'size': 'modal-medium',
            'options': {
                dismissible: false
            }
        });

        return mdl;
    }

    $(document).on('click', '[data-trigger=delete]', function() {
        var mdl = modal({
                'id': 'alert',
                'body': 'Silmek istediğinizden emin misiniz?',
                'size': 'modal-small',
                'title': 'Sil',
                'options': {}
            });

            mdl.find('.modal-footer')
               .html([
                    $('<a />', {
                        'href': '#',
                        'class': 'modal-close waves-effect btn-flat',
                        'html': buttons.cancel
                    }),
                    $('<span />', {
                        'html': ' '
                    }),
                    $('<a />', {
                        'href': '#',
                        'class': 'waves-effect btn red json',
                        'html': buttons.ok,
                        'data-href': '{{ route('admin.twitter.token') }}',
                        'data-method': 'delete',
                        'data-id': $(this).data('id'),
                        'data-callback': '__delete'
                    })
               ])
    })

    $(document).on('click', '[data-trigger=create]', function() {
        var _modal = form_modal();
            _modal.find('.modal-title').html('Token Oluştur')

        var form = _modal.find('form#form')

        $('input[name=consumer_key]').val('')
        $('input[name=consumer_secret]').val('')
        $('input[name=access_token]').val('')
        $('input[name=access_token_secret]').val('')
        $('input[name=off_limit]').val('10')

        $('[data-trigger=delete]').removeAttr('data-id').addClass('d-none')

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
                classes: 'green'
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
                classes: 'green'
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
                classes: 'green'
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

            $('input[name=consumer_key]').val(obj.data.consumer_key)
            $('input[name=consumer_secret]').val(obj.data.consumer_secret)
            $('input[name=access_token]').val(obj.data.access_token)
            $('input[name=access_token_secret]').val(obj.data.access_token_secret)
            $('input[name=off_limit]').val(obj.data.off_limit)

            $('[data-trigger=delete]').data('id', obj.data.id).removeClass('d-none')

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
                            'options': {}
                        });

                        _mdl.find('.modal-footer')
                           .html([
                                $('<a />', {
                                    'href': '#',
                                    'class': 'modal-close waves-effect btn-flat',
                                    'html': buttons.ok
                                })
                           ])
                }, 500)
            }
        }
    }
@endpush

@push('local.styles')
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

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/card-header.jpg') }}" alt="Token Yönetimi" />
            <span class="card-title">Token Yönetimi</span>

            <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-trigger="create">
                <i class="material-icons black-text">add</i>
            </a>
        </div>
        <div class="card-content">
            <p class="green-text">Aktif, çalışıyor.</p>
            <p class="orange-text">İşlem tamamlandı ve kuyruk sonlandırıldı. Token boşa çıkacak.</p>
            <p class="red-text">Hata alındı, ilgilenilmesi gerekiyor.</p>
            <p class="grey-text">İhtiyaç dışı, kullanılmıyor. Gerektiğinde kullanılacak.</p>
        </div>
        <div class="collection load" 
             id="collections"
             data-href="{{ route('admin.twitter.tokens.json') }}"
             data-callback="__collections"
             data-nothing>
            <div class="collection-item nothing d-none">
                <div class="not-found">
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">wb_sunny</i>
                    <p>Token Yok</p>
                </div>
            </div>
            <a
                class="collection-item model d-none flex-wrap red z-depth-4 waves-effect json"
                data-href="{{ route('admin.twitter.token') }}"
                data-method="get"
                data-callback="__get"
                href="#">
                <input data-name="id" readonly type="text" />
                <input data-name="pid" readonly type="text" class="right-align" />
                <input data-name="sh" readonly type="text" class="white-text" />
                <input data-name="value" readonly type="text" class="white-text" />
            </a>
        </div>
    </div>

    @component('components.loader')
        @slot('color', 'purple')
        @slot('id', 'home-loader')
    @endcomponent
@endsection

@section('dock')
	@include('crawlers.twitter._menu', [ 'active' => 'tokens' ])
@endsection
