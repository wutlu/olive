@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
        	'text' => 'Carousel Yönetimi'
        ]
    ]
])

@push('local.scripts')
    function __collections(__, obj)
    {
        var ul = $('#collections');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                var i = 1;

                $.each(obj.hits, function(key, o) {
                    var selector = $('[data-id=' + o.id + '].collection-item');
                    var item = selector.length ? selector : item_model.clone();

                        item.removeClass('model hide red-text')
                            .addClass('_tmp d-flex')
                            .addClass(o.visibility ? '' : 'red-text')
                            .attr('data-id', o.id)

                        item.find('[data-name=trigger]').attr('data-id', o.id)
                        item.find('[data-name=title]').html(o.title)
                        item.find('[data-name=created-at]').html(o.created_at).attr('data-time', o.created_at)

                        if (!selector.length)
                        {
                            item.appendTo(ul)
                        }

                    i++;
                })
            }

            $('[data-name=count]').html(obj.hits.length)

            $('#home-loader').hide()
        }
    }

    function form_modal()
    {
        var mdl = modal({
            'id': 'carousel',
            'body': $('<form />', {
                'action': '{{ route('admin.carousel') }}',
                'id': 'form',
                'class': 'json',
                'data-method': 'patch',
                'html': [
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<input />', {
                                'id': 'title',
                                'name': 'title',
                                'type': 'text',
                                'class': 'validate',
                                'data-length': 48
                            }),
                            $('<label />', {
                                'for': 'title',
                                'html': 'Başlık'
                            }),
                            $('<span />', {
                                'class': 'helper-text'
                            })
                        ]
                    }),
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<textarea />', {
                                'id': 'description',
                                'name': 'description',
                                'class': 'materialize-textarea validate',
                                'data-length': 128
                            }),
                            $('<label />', {
                                'for': 'description',
                                'html': 'Açıklama'
                            }),
                            $('<span />', {
                                'class': 'helper-text'
                            })
                        ]
                    }),
                    $('<div />', {
                        'class': 'd-flex',
                        'html': [
                            $('<div />', {
                                'class': 'input-field',
                                'html': [
                                    $('<input />', {
                                        'id': 'button_action',
                                        'name': 'button_action',
                                        'type': 'text',
                                        'class': 'validate',
                                        'data-length': 255
                                    }),
                                    $('<label />', {
                                        'for': 'button_action',
                                        'html': 'Aksiyon Adresi'
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
                                        'id': 'button_text',
                                        'name': 'button_text',
                                        'type': 'text',
                                        'class': 'validate',
                                        'data-length': 32
                                    }),
                                    $('<label />', {
                                        'for': 'button_text',
                                        'html': 'Buton Yazısı'
                                    }),
                                    $('<span />', {
                                        'class': 'helper-text'
                                    })
                                ]
                            })
                        ]
                    }),
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<select />', {
                                'id': 'pattern',
                                'name': 'pattern',
                                'type': 'text',
                                'class': 'validate',
                                'html': [
                                    $('<option />', {
                                        'value': '',
                                        'html': 'Seçin',
                                        'selected': true
                                    }),

                                    @foreach (config('system.carousel.patterns') as $key => $pattern)
                                        $('<option />', {
                                            'value': '{{ $key }}',
                                            'html': '{{ $pattern }}'
                                        }),
                                    @endforeach
                                ]
                            }),
                            $('<label />', {
                                'for': 'pattern',
                                'html': 'Carousel Türü'
                            })
                        ]
                    }),
                    $('<div />', {
                        'class': 'collection',
                        'html': $('<label />', {
                            'class': 'collection-item waves-effect d-block',
                            'html': [
                                $('<input />', {
                                    'name': 'visibility',
                                    'id': 'visibility',
                                    'value': '1',
                                    'type': 'checkbox'
                                }),
                                $('<span />', {
                                    'html': 'Görünürlük'
                                })
                            ]
                        })
                    })
                ]
            }),
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
            ],
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
                        'data-href': '{{ route('admin.carousel') }}',
                        'data-method': 'delete',
                        'data-id': $(this).data('id'),
                        'data-callback': '__delete'
                    })
                ],
                'options': {}
            });
    })

    $(document).on('click', '[data-trigger=create]', function() {
        var _modal = form_modal();
            _modal.find('.modal-title').html('Carousel Oluştur')

        var form = _modal.find('form#form')

        $('input[name=title]').val('').characterCounter()
        $('textarea[name=description]').val('').characterCounter()
        $('input[name=button_action]').val('').characterCounter()
        $('input[name=button_text]').val('').characterCounter()
        $('select[name=pattern]').val('').formSelect()
        $('input[name=visibility]').prop('checked', false)

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
                $('#modal-carousel').modal('close')
            }, 200)

            M.toast({
                html: 'Carousel silindi.',
                classes: 'green darken-2'
            })

            vzAjax($('#collections'))
        }
    }

    function __update(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({
                html: 'Carousel Güncellendi',
                classes: 'green darken-2'
            })

            vzAjax($('#collections'))

            $('#modal-carousel').modal('close')
        }
    }

    function __create(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({
                html: 'Carousel Oluşturuldu',
                classes: 'green darken-2'
            })

            vzAjax($('#collections'))

            $('#modal-carousel').modal('close')
        }
    }

    function __get(__, obj)
    {
        if (obj.status == 'ok')
        {
            var _modal = form_modal();
                _modal.find('.modal-title').html('Carousel Güncelle')

            var form = _modal.find('form#form')

            $('input[name=title]').val(obj.data.title).characterCounter()
            $('textarea[name=description]').val(obj.data.description).characterCounter()
            $('input[name=button_action]').val(obj.data.button_action).characterCounter()
            $('input[name=button_text]').val(obj.data.button_text).characterCounter()
            $('select[name=pattern]').val(obj.data.pattern).formSelect()
            $('input[name=visibility]').prop('checked', obj.data.visibility)

            $('[data-trigger=delete]').data('id', obj.data.id).removeClass('hide')

            form.data('id', obj.data.id)
            form.attr('method', 'patch')
            form.data('callback', '__update')

            M.updateTextFields()
        }
    }
@endpush

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/card-header.jpg') }}" alt="Carousel Yönetimi" />
            <span class="card-title">Carousel Yönetimi</span>

            <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-trigger="create">
                <i class="material-icons grey-text text-darken-2">add</i>
            </a>
        </div>
        <div class="card-content grey lighten-4">
            <span data-name="count">0</span> / 10
        </div>
        <ul class="collection load" 
             id="collections"
             data-href="{{ route('admin.carousels.json') }}"
             data-callback="__collections"
             data-method="post"
             data-nothing>
            <li class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </li>
            <li class="collection-item model hide justify-content-between">
                <div>
                    <p data-name="title"></p>
                    <time data-name="created-at" class="timeago grey-text"></time>
                </div>
                <a
                    href="#"
                    class="btn-flat waves-effect ml-auto json"
                    data-href="{{ route('admin.carousel') }}"
                    data-method="post"
                    data-callback="__get"
                    data-name="trigger">
                    <i class="material-icons">create</i>
                </a>
            </li>
        </ul>
    </div>

    @component('components.loader')
        @slot('color', 'cyan')
        @slot('id', 'home-loader')
    @endcomponent
@endsection
