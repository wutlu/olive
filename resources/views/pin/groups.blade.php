@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Pin Grupları'
        ]
    ]
])

@push('local.scripts')
    function __pin_groups(__, obj)
    {
        var ul = $('#pin-groups');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp d-flex').attr('data-id', o.id)

                        item.find('[data-name=edit]').attr('data-group_id', o.id)
                        item.find('[data-trigger=pin-go]').html(o.name).attr('data-id', o.id)
                        item.find('[data-name=created-at]').attr('data-time', o.created_at).html(o.created_at)
                        item.find('[data-name=count]').html(o.pins_count + ' pin')

                        item.appendTo(ul)
                })
            }
        }
    }

    function pin_group_modal()
    {
        var mdl = modal({
            'id': 'pin-group',
            'body': $('<form />', {
                'action': '{{ route('pin.group') }}',
                'id': 'pin-group-form',
                'class': 'json',
                'data-method': 'post',
                'data-callback': '__pin_group_callback',
                'html': [
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<input />', {
                                'id': 'name',
                                'name': 'name',
                                'type': 'text',
                                'class': 'validate',
                                'data-length': 32
                            }),
                            $('<label />', {
                                'for': 'name',
                                'html': 'Grup Adı'
                            }),
                            $('<span />', {
                                'class': 'helper-text',
                                'html': 'Pin grubu için isim girin.'
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
                    'data-trigger': 'delete-pin-group',
                    'href': '#',
                    'class': 'waves-effect btn-flat red-text hide',
                    'html': buttons.remove
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<button />', {
                    'type': 'submit',
                    'class': 'waves-effect btn-flat',
                    'data-submit': 'form#pin-group-form',
                    'html': buttons.ok
                })
            ]
        });

        mdl.find('input[name=name]').characterCounter()

        M.updateTextFields()

        return mdl;
    }

    $(document).on('click', '[data-trigger=create-pin-group]', function() {
        var mdl = pin_group_modal();
            mdl.find('.modal-title').html('Grup Oluştur')
            mdl.find('form#pin-group-form').data('method', 'put')

            mdl.find('[name=name]').val('')

        $('[data-trigger=delete-pin-group]').removeAttr('data-id').addClass('hide')
    })

    function __get_pin_group(__, obj)
    {
        if (obj.status == 'ok')
        {
            var mdl = pin_group_modal();
                mdl.find('.modal-title').html('Grup Güncelle')
                mdl.find('form#pin-group-form').data('id', obj.data.id)
                                               .data('method', 'patch')
                mdl.find('[name=name]').val(obj.data.name)

            $('[data-trigger=delete-pin-group]').data('id', obj.data.id).removeClass('hide')
        }
    }

    function __pin_group_callback(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-pin-group').modal('close')

            if (obj.type == 'created')
            {
                vzAjax($('#pin-groups').data('skip', 0).addClass('json-clear'))
            }
            else if (obj.type == 'updated')
            {
                $('#pin-groups').children('[data-id=' + obj.data.id + ']').find('[data-trigger=pin-go]').html(obj.data.name)
            }

            M.toast({
                html: obj.type == 'created' ? 'Pin Grubu Oluşturuldu' : obj.type == 'updated' ? 'Pin Grubu Güncellendi' : 'İşlem Gerçekleşti',
                classes: 'green darken-2'
            })
        }
    }

    $(document).on('click', '[data-trigger=delete-pin-group]', function() {
        var mdl = modal({
                'id': 'pin-group-alert',
                'body': 'Pin grubu silinecek?',
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
                        'data-href': '{{ route('pin.group') }}',
                        'data-method': 'delete',
                        'data-id': $(this).data('id'),
                        'data-callback': '__delete_pin_group'
                    })
                ]
            });
    })

    function __delete_pin_group(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#pin-groups').children('[data-id=' + obj.data.id + ']').remove()

            $('#modal-pin-group-alert').modal('close')

            setTimeout(function() {
                $('#modal-pin-group').modal('close')
            }, 200)

            M.toast({
                html: 'Pin Grubu Silindi',
                classes: 'red darken-2'
            })

            vzAjax($('#pin-groups').data('skip', 0).addClass('json-clear'))
        }
    }
@endpush

@section('content')
    <div class="card with-bg">
        <div class="card-image">
            <img src="{{ asset('img/md-s/13.jpg') }}" alt="Image" />
            <span class="card-title white-text d-flex">
                <i class="material-icons align-self-center mr-1">speaker_notes</i>
                Pin Grupları
            </span>
            <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-trigger="create-pin-group">
                <i class="material-icons grey-text text-darken-2">add</i>
            </a>
        </div>
        <div class="card-content">
            <p class="grey-text text-darken-2">Araştırmalarınız sonucu elde ettiğiniz ham verileri pinleme gruplarında saklayabilirsiniz.</p>
            <p class="grey-text text-darken-2">Ayrıca pinlediğiniz verileri PDF halinde rapor alabilirsiniz.</p>
        </div>
        <nav class="nav-half">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#pin-groups"
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                    <i class="material-icons">close</i>
                </div>
            </div>
        </nav>
        <ul id="pin-groups"
             class="collection load json-clear" 
             data-href="{{ route('pin.groups') }}"
             data-skip="0"
             data-take="5"
             data-include="string"
             data-more-button="#pin-groups-more_button"
             data-callback="__pin_groups"
             data-method="post"
             data-loader="#home-loader"
             data-nothing>
            <li class="collection-item nothing hide">
                @component('components.nothing')
                    @slot('size', 'small')
                @endcomponent
            </li>
            <li data-name="item" class="collection-item model hide">
                <span>
                    <a
                        style="margin: 0 1rem 0 0;"
                        class="json"
                        data-name="edit"
                        data-href="{{ route('pin.group') }}"
                        data-method="post"
                        data-callback="__get_pin_group"
                        href="#">
                        <i class="material-icons">create</i>        
                    </a>
                </span>
                <span>
                	<a
                        data-trigger="pin-go"
                        data-name="pin.pins"
                        data-href="{{ route('route.generate.id') }}"
                        data-method="post"
                        data-callback="__go"
                        class="json d-table"
                        href="#"></a>
                    <time data-name="created-at" class="timeago grey-text"></time>
                </span>
                <small data-name="count" class="ml-auto"></small>
            </li>
        </ul>

        @component('components.loader')
            @slot('color', 'cyan')
            @slot('id', 'home-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>

    <div class="center-align">
        <button class="btn-flat waves-effect hide json"
                id="pin-groups-more_button"
                type="button"
                data-json-target="#pin-groups">Öncekiler</button>
    </div>
@endsection
