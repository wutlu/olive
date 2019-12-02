@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
        	'text' => '🐞 Carousel Yönetimi'
        ]
    ],
    'footer_hide' => true
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

                        item.removeClass('model hide').addClass('_tmp').attr('data-id', o.id)

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
        }
    }

    function form_modal()
    {
        return modal({
            'id': 'carousel',
            'body': $('<form />', {
                'action': '{{ route('admin.carousel') }}',
                'id': 'form',
                'class': 'json',
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
                                'data-length': 1000
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
                        'html': [
                            $('<label />', {
                                'class': 'collection-item waves-effect d-block',
                                'html': [
                                    $('<input />', {
                                        'name': 'carousel',
                                        'id': 'carousel',
                                        'value': '1',
                                        'type': 'checkbox'
                                    }),
                                    $('<span />', {
                                        'html': 'Carousel'
                                    })
                                ]
                            }),
                            $('<label />', {
                                'class': 'collection-item waves-effect d-block',
                                'html': [
                                    $('<input />', {
                                        'name': 'modal',
                                        'id': 'modal',
                                        'value': '1',
                                        'type': 'checkbox'
                                    }),
                                    $('<span />', {
                                        'html': 'Modal'
                                    })
                                ]
                            })
                        ]
                    })
                ]
            }),
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
            ],
            'size': 'modal-medium',
            'options': {
                dismissible: false
            }
        });
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
                    'html': keywords.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<a />', {
                    'href': '#',
                    'class': 'waves-effect btn-flat red-text json',
                    'html': keywords.ok,
                    'data-href': '{{ route('admin.carousel') }}',
                    'data-method': 'delete',
                    'data-id': $(this).data('id'),
                    'data-callback': '__delete'
                })
            ],
            'options': {}
        })
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
        $('input[name=carousel]').prop('checked', false)
        $('input[name=modal]').prop('checked', false)

        $('[data-trigger=delete]').removeAttr('data-id').addClass('hide')

        form.removeAttr('data-id')
        form.attr('method', 'put')
        form.data('callback', '__create')

        M.textareaAutoResize($('textarea[name=description]'))
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
                html: 'Carousel Silindi',
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
            $('input[name=carousel]').prop('checked', obj.data.carousel)
            $('input[name=modal]').prop('checked', obj.data.modal)

            $('[data-trigger=delete]').data('id', obj.data.id).removeClass('hide')

            form.data('id', obj.data.id)
            form.attr('method', 'patch')
            form.data('callback', '__update')

            M.textareaAutoResize($('textarea[name=description]'))
        }
    }
@endpush

@section('content')
    <div class="card with-bg">
        <div class="card-image">
            <img src="{{ asset('img/md-s/21.jpg') }}" alt="Image" />
            <span class="card-title white-text">
                Carousel Yönetimi
            </span>
            <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-trigger="create">
                <i class="material-icons grey-text text-darken-2">add</i>
            </a>
        </div>
        <ul class="collection load" 
             id="collections"
             data-href="{{ route('admin.carousels.json') }}"
             data-callback="__collections"
             data-method="post"
             data-loader="#home-loader"
             data-nothing>
            <li class="collection-item nothing hide">
                @component('components.nothing')
                    @slot('size', 'small')
                @endcomponent
            </li>
            <li class="collection-item model hide">
                <div class="d-flex justify-content-between">
                    <span class="align-self-center">
                        <p data-name="title" class="mb-0"></p>
                        <time data-name="created-at" class="timeago grey-text"></time>
                    </span>
                    <a
                        href="#"
                        class="btn-flat waves-effect json align-self-center"
                        data-href="{{ route('admin.carousel') }}"
                        data-method="post"
                        data-callback="__get"
                        data-name="trigger">
                        <i class="material-icons">create</i>
                    </a>
                </div>
            </li>
        </ul>

        @component('components.loader')
            @slot('color', 'blue-grey')
            @slot('id', 'home-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>
@endsection
