@push('local.styles')
    #pin-groups.collection > .collection-item > label {
        max-width: calc(100% - 48px);
        overflow: hidden;
    }
@endpush

<div class="card with-bg" id="pin-groups-dock">
    <div class="card-image">
        <img src="{{ asset('img/md/13.jpg') }}" alt="Image" />
        <span class="card-title white-text d-flex">
            <i class="material-icons align-self-center mr-1">speaker_notes</i>
            Pin Grupları
        </span>
    </div>
    <div class="card-image">
        <a href="#" class="btn-floating halfway-fab waves-effect blue darken-2" data-trigger="create-pin-group">
            <i class="material-icons">add</i>
        </a>
    </div>
    <div class="card-content grey-text">
        <span data-name="display-pin-group">0</span> / <span data-name="total-pin-group">0</span>
    </div>

    <ul id="pin-groups"
        class="collection load json-clear mb-0" 
        data-href="{{ route('pin.groups') }}"
        data-skip="0"
        data-take="5"
        data-more-button="#pin-groups-more_button"
        data-callback="__pin_groups"
        data-method="post"
        data-loader="#pin-groups-loader"
        data-nothing>
        <li class="collection-item nothing hide">
            @component('components.nothing')
                @slot('size', 'small')
            @endcomponent
        </li>
        <li data-name="item" class="collection-item model hide">
            <a
                class="btn-floating btn-small waves-effect json align-self-center white mr-1"
                data-name="edit"
                data-href="{{ route('pin.group') }}"
                data-method="post"
                data-callback="__get_pin_group"
                href="#">
                <i class="material-icons grey-text text-darken-2">create</i>        
            </a>
            <label class="align-self-center">
                <input name="group_id" type="radio" />
                <span class="d-flex">
                    <a
                        data-trigger="pin-go"
                        data-name="pin.pins"
                        data-href="{{ route('route.generate.id') }}"
                        data-method="post"
                        data-callback="__go"
                        class="json d-table"
                        href="#"></a>
                </span>
            </label>
        </li>
    </ul>

    @component('components.loader')
        @slot('color', 'cyan')
        @slot('id', 'pin-groups-loader')
        @slot('class', 'card-loader-unstyled')
    @endcomponent

    <div class="card-content center-align">
        <a href="{{ route('pin.groups') }}" class="btn-flat waves-effect">Tüm Gruplar</a>
    </div>
</div>

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
                        item.find('[data-name=count]').html(o.pins_count + ' pin')
                        item.find('[name=group_id]').val(o.id)

                        item.appendTo(ul)
                })
            }

            $('[data-name=display-pin-group]').html(ul.children('.collection-item._tmp').length)
            $('[data-name=total-pin-group]').html(obj.total)
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
                                'id': 'group_name',
                                'name': 'group_name',
                                'data-alias': 'name',
                                'type': 'text',
                                'class': 'validate',
                                'data-length': 32
                            }),
                            $('<label />', {
                                'for': 'group_name',
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

        return mdl;
    }

    $(document).on('click', '[data-trigger=create-pin-group]', function() {
        var mdl = pin_group_modal();
            mdl.find('.modal-title').html('Grup Oluştur')
            mdl.find('form#pin-group-form').data('method', 'put')

            mdl.find('[name=group_name]').val('')

        $('[data-trigger=delete-pin-group]').removeAttr('data-id').addClass('hide')
    })

    function __get_pin_group(__, obj)
    {
        if (obj.status == 'ok')
        {
            var mdl = pin_group_modal();
                mdl.find('.modal-title').html('Grup Güncelle')
                mdl.find('form#pin-group-form').data('id', obj.data.id).data('method', 'patch')
                mdl.find('[name=group_name]').val(obj.data.name)

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

                if ($('[data-name=total-pin-group]').html() == 0)
                {
                    $('[data-trigger=create-pin-group]').removeClass('pulse')
                }
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
        return modal({
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
        })
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

    function __pin_dock(__)
    {
        $('#pin-groups-dock').addClass('active')

        if ($('[data-name=total-pin-group]').html() == 0)
        {
            $('[data-trigger=create-pin-group]').addClass('pulse')

            M.toast({
                html: 'Öncelikle bir pin grubu oluşturmalısınız.',
                classes: 'blue darken-2'
            })
        }
        else
        {
            M.toast({
                html: 'Lütfen bir pin grubu seçin!',
                classes: 'red'
            })
        }
    }

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
@endpush
