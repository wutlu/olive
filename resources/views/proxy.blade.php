@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
        	'text' => 'Vekil Sunucu Yönetimi'
        ]
    ]
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
                    var per = o.health * 10;
                    var selector = $('[data-id=' + o.id + '].collection-item');
                    var item = selector.length ? selector : item_model.clone();

                        item.find('[data-name=proxy]').html(o.proxy)
                        item.find('[data-name=health]').css({
                            'width': per + '%'
                        })
                        .removeClass('red orange green')
                        .addClass(o.health <= 5 ? 'red' : o.health <= 7 ? 'orange' : 'green')
                        .parent('.progress')
                        .removeClass('red orange green')
                        .addClass(o.health <= 5 ? 'red' : o.health <= 7 ? 'orange' : 'green')

                        item.removeClass('model d-none')
                            .addClass('_tmp')
                            .attr('data-id', o.id)

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
                        'data-href': '{{ route('admin.proxy') }}',
                        'data-method': 'delete',
                        'data-id': $(this).data('id'),
                        'data-callback': '__delete'
                    })
               ])
    })

    $(document).on('click', '[data-trigger=create]', function() {
        var _modal = form_modal();
            _modal.find('.modal-title').html('Vekil Sunucu Oluştur')

        var form = _modal.find('form#form')

        $('input[name=proxy]').val('')

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
                $('#modal-proxy').modal('close')
            }, 200)

            M.toast({
                html: 'Vekil Sunucu silindi.',
                classes: 'green'
            })
        }
    }

    function __update(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({
                html: 'Vekil Sunucu Güncellendi',
                classes: 'green'
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
                classes: 'green'
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

            $('input[name=proxy]').val(obj.data.proxy)

            $('[data-trigger=delete]').data('id', obj.data.id).removeClass('d-none')

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
            <img src="{{ asset('img/card-header.jpg') }}" alt="Vekil Sunucu Yönetimi" />
            <span class="card-title">Vekil Sunucu Yönetimi</span>

            <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-trigger="create">
                <i class="material-icons black-text">add</i>
            </a>
        </div>
        <div class="card-content red lighten-5">
            <p>Veri toplamada daha yüksek erişim sağlamak için birden fazla vekil sunucu kullanın.</p>
            <p>Vekil sunucu yaşam değerleri otomatize bir şekilde sürekli olarak kontrol edilir. Yaşam değeri düşük sunucular, yaşam değeri normal duruma gelene kadar kullanılmayacaktır.</p>
        </div>
        <div class="collection load" 
             id="collections"
             data-href="{{ route('admin.proxies.json') }}"
             data-callback="__collections"
             data-nothing>
            <div class="collection-item nothing d-none">
                <div class="not-found">
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">wb_sunny</i>
                    <p>Vekil Sunucu Yok</p>
                </div>
            </div>
            <a
                class="collection-item model d-none waves-effect json"
                data-href="{{ route('admin.proxy') }}"
                data-method="get"
                data-callback="__get"
                href="#">
                <span data-name="proxy"></span>
                <div class="progress lighten-5">
                    <div data-name="health" class="determinate"></div>
                </div>
            </a>
        </div>
    </div>

    @component('components.loader')
        @slot('color', 'purple')
        @slot('id', 'home-loader')
    @endcomponent
@endsection
