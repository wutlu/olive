@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
        	'text' => '🐞 Vekil Sunucu Yönetimi'
        ]
    ],
    'footer_hide' => true
])

@push('local.scripts')
    var collection_timer;

    function __collection(__, obj)
    {
        var item_model = __.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var per = o.health * 10;
                    var selector = $('[data-id=' + o.id + '].collection-item');
                    var item = selector.length ? selector : item_model.clone();

                        item.find('[data-name=proxy]').html('ipv' + o.ipv + ' ' + o.proxy)
                        item.find('[data-name=health]').css({
                                'width': per + '%'
                            })
                            .removeClass('red orange green')
                            .addClass(o.health <= 5 ? 'red' : o.health <= 7 ? 'orange' : 'green')
                            .parent('.progress')
                            .removeClass('red orange green')
                            .addClass(o.health <= 5 ? 'red' : o.health <= 7 ? 'orange' : 'green')

                        item.removeClass('model hide')
                            .addClass('_tmp')
                            .attr('data-id', o.id)

                        if (!selector.length)
                        {
                            item.appendTo(__)
                        }
                })
            }
        }

        window.clearTimeout(collection_timer)

        collection_timer = window.setTimeout(function() {
            vzAjax($('#collection'))
        }, 5000)
    }

    function form_modal()
    {
        return modal({
            'id': 'proxy',
            'body': $('<form />', {
                'action': '{{ route('admin.proxy') }}',
                'id': 'form',
                'class': 'json',
                'html': [
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<input />', {
                                'id': 'proxy',
                                'name': 'proxy',
                                'type': 'text',
                                'class': 'validate',
                                'data-length': 255
                            }),
                            $('<label />', {
                                'for': 'proxy',
                                'html': 'Vekil Sunucu Adresi'
                            }),
                            $('<span />', {
                                'class': 'helper-text'
                            })
                        ]
                    }),
                    $('<div />', {
                        'class': 'd-flex mb-2',
                        'html': [
                            $('<label />', {
                                'class': 'flex-fill align-self-center',
                                'html': [
                                    $('<input />', {
                                        'type': 'radio',
                                        'name': 'ipv',
                                        'id': 'ipv-4',
                                        'value': '4'
                                    }),
                                    $('<span />', {
                                        'html': 'ipv4'
                                    })
                                ]
                            }),
                            $('<label />', {
                                'class': 'flex-fill align-self-center',
                                'html': [
                                    $('<input />', {
                                        'type': 'radio',
                                        'name': 'ipv',
                                        'id': 'ipv-6',
                                        'value': '6'
                                    }),
                                    $('<span />', {
                                        'html': 'ipv6'
                                    })
                                ]
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
                    'html': keywords.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<a />', {
                    'data-trigger': 'delete',
                    'href': '#',
                    'class': 'waves-effect btn-flat red-text hide',
                    'html': keywords.remove
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<button />', {
                    'type': 'submit',
                    'class': 'waves-effect btn-flat',
                    'data-submit': 'form#form',
                    'html': keywords.ok
                })
            ]
        });
    }

    $(document).on('click', '[data-trigger=delete]', function() {
        var mdl = modal({
                'id': 'alert',
                'body': 'Vekil sunucu silinecek?',
                'size': 'modal-small',
                'title': 'Sil',
                'options': {},
                'footer': [
                    $('<a />', {
                        'href': '#',
                        'class': 'modal-close waves-effect btn-flat grey-text',
                        'html': keywords.cancel
                    }),
                    $('<span />', {
                        'html': ' '
                    }),
                    $('<a />', {
                        'href': '#',
                        'class': 'waves-effect btn-flat red-text json',
                        'html': keywords.ok,
                        'data-href': '{{ route('admin.proxy') }}',
                        'data-method': 'delete',
                        'data-id': $(this).data('id'),
                        'data-callback': '__delete'
                    })
                ]
            });
    })

    $(document).on('click', '[data-trigger=create]', function() {
        var _modal = form_modal();
            _modal.find('.modal-title').html('Vekil Sunucu Oluştur')

        var form = _modal.find('form#form')

        $('input[name=proxy]').val('').characterCounter()
        $('input[name=ipv]').prop('checked', false)

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
                $('#modal-proxy').modal('close')
            }, 200)

            M.toast({
                html: 'Vekil Sunucu Silindi',
                classes: 'green darken-2'
            })
        }
    }

    function __update(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({
                html: 'Vekil Sunucu Güncellendi',
                classes: 'green darken-2'
            })

            $('#modal-proxy').modal('close')
        }
    }

    function __create(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({
                html: 'Vekil Sunucu Oluşturuldu',
                classes: 'green darken-2'
            })

            $('#modal-proxy').modal('close')
        }
    }

    function __get(__, obj)
    {
        if (obj.status == 'ok')
        {
            var _modal = form_modal();
                _modal.find('.modal-title').html('Vekil Sunucu Güncelle')

            var form = _modal.find('form#form')

            $('input[name=proxy]').val(obj.data.proxy).characterCounter()
            $('input#ipv-' + obj.data.ipv).prop('checked', true)

            $('[data-trigger=delete]').data('id', obj.data.id).removeClass('hide')

            form.data('id', obj.data.id)
            form.attr('method', 'patch')
            form.data('callback', '__update')
        }
    }
@endpush

@section('content')
    <div class="card with-bg">
        <div class="card-image">
            <img src="{{ asset('img/md-s/21.jpg') }}" alt="Image" />
            <span class="card-title white-text d-flex">
                <i class="material-icons align-self-center mr-1">date_range</i>
                Vekil Sunucu Yönetimi
            </span>
            <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-trigger="create">
                <i class="material-icons grey-text text-darken-2">add</i>
            </a>
        </div>
        <div class="collection load"
             id="collection"
             data-href="{{ route('admin.proxies.json') }}"
             data-callback="__collection"
             data-method="post"
             data-loader="#home-loader"
             data-nothing>
            <div class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </div>
            <a
                class="collection-item model hide json"
                data-href="{{ route('admin.proxy') }}"
                data-method="post"
                data-callback="__get"
                href="#">
                <span data-name="proxy"></span>
                <div class="progress lighten-5">
                    <div data-name="health" class="determinate"></div>
                </div>
            </a>
        </div>

        @component('components.loader')
            @slot('color', 'blue-grey')
            @slot('id', 'home-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>
@endsection
