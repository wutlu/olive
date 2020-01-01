@push('local.styles')
    #pin-groups.collection > .collection-item > label {
        max-width: calc(100% - 48px);
        overflow: hidden;
    }
@endpush

<div id="archive-dock">
    <div class="card card-unstyled">
        <div class="card-content">
            <span class="card-title d-flex blue-grey-text text-darken-2">
                <i class="material-icons align-self-center mr-1">archive</i>
                Arşivler
            </span>
        </div>
        <div class="card-image">
            <a href="#" class="btn-floating halfway-fab waves-effect blue-grey darken-2" data-trigger="create-pin_group">
                <i class="material-icons">add</i>
            </a>
        </div>
        <div class="card-content grey-text text-darken-2">
            <span data-name="display-pin-group">0</span> / <span data-name="total-pin-group">0</span>
        </div>

        <ul id="pin-groups"
            class="collection collection-unstyled load json-clear mb-0" 
            data-href="{{ route('pin.groups') }}"
            data-skip="0"
            data-take="5"
            data-more-button="#pin-groups-more_button"
            data-callback="__archive_groups"
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
                    data-callback="__get_archive_group"
                    href="#">
                    <i class="material-icons grey-text text-darken-2">create</i>        
                </a>
                <label class="align-self-center">
                    <input name="archive_id" type="radio" />
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
            @slot('color', 'blue-grey')
            @slot('id', 'pin-groups-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent

        <div class="card-content center-align">
            <a href="{{ route('pin.groups') }}" class="btn-flat waves-effect">Tüm Arşivler</a>
        </div>
    </div>
</div>

@push('local.scripts')
    function __archive_groups(__, obj)
    {
        var item_model = __.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp d-flex').attr('data-id', o.id)

                        item.find('[data-name=edit]').attr('data-archive_id', o.id)
                        item.find('[data-trigger=pin-go]').html(o.name).attr('data-id', o.id)
                        item.find('[data-name=count]').html(o.pins_count + ' içerik')
                        item.find('[name=archive_id]').val(o.id)

                        item.appendTo(__)
                })
            }

            $('[data-name=display-pin-group]').html(__.children('.collection-item._tmp').length)
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
                'data-callback': '__archive_group_callback',
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
                                'html': 'Arşiv Adı'
                            }),
                            $('<span />', {
                                'class': 'helper-text',
                                'html': 'Arşiv için isim girin.'
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
                    'data-trigger': 'delete-pin-group',
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
                    'data-submit': 'form#pin-group-form',
                    'html': keywords.ok
                })
            ]
        });

        mdl.find('input[name=name]').characterCounter()

        return mdl;
    }

    $(document).on('click', '[data-trigger=create-pin_group]', function() {
        var mdl = pin_group_modal();
            mdl.find('.modal-title').html('Arşiv Oluştur')
            mdl.find('form#pin-group-form').data('method', 'put')

            mdl.find('[name=group_name]').val('').focus()

        $('[data-trigger=delete-pin-group]').removeAttr('data-id').addClass('hide')
    })

    function __get_archive_group(__, obj)
    {
        if (obj.status == 'ok')
        {
            var mdl = pin_group_modal();
                mdl.find('.modal-title').html('Arşiv Güncelle')
                mdl.find('form#pin-group-form').data('id', obj.data.id).data('method', 'patch')
                mdl.find('[name=group_name]').val(obj.data.name).focus()

            $('[data-trigger=delete-pin-group]').data('id', obj.data.id).removeClass('hide')
        }
    }

    function __archive_group_callback(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-pin-group').modal('close')

            if (obj.type == 'created')
            {
                vzAjax($('#pin-groups').data('skip', 0).addClass('json-clear'))

                if ($('[data-name=total-pin-group]').html() == 0)
                {
                    $('[data-trigger=create-pin_group]').removeClass('pulse')
                }
            }
            else if (obj.type == 'updated')
            {
                $('#pin-groups').children('[data-id=' + obj.data.id + ']').find('[data-trigger=pin-go]').html(obj.data.name)
            }

            M.toast({
                html: obj.type == 'created' ? 'Arşiv Oluşturuldu' : obj.type == 'updated' ? 'Arşiv Güncellendi' : 'İşlem Gerçekleşti',
                classes: 'green darken-2'
            })
        }
    }

    $(document).on('click', '[data-trigger=delete-pin-group]', function() {
        return modal({
            'id': 'pin-group-alert',
            'body': 'Arşiv silinecek?',
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
                    'data-href': '{{ route('pin.group') }}',
                    'data-method': 'delete',
                    'data-id': $(this).data('id'),
                    'data-callback': '__delete_archive_group'
                })
            ]
        })
    })

    function __delete_archive_group(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#pin-groups').children('[data-id=' + obj.data.id + ']').remove()

            $('#modal-pin-group-alert').modal('close')

            setTimeout(function() {
                $('#modal-pin-group').modal('close')
            }, 200)

            M.toast({
                html: 'Arşiv Silindi',
                classes: 'red darken-2'
            })

            vzAjax($('#pin-groups').data('skip', 0).addClass('json-clear'))
        }
    }

    function __archive_dock(__)
    {
        $('#archive-dock').addClass('active')

        if ($('[data-name=total-pin-group]').html() == 0)
        {
            $('[data-trigger=create-pin_group]').addClass('pulse')

            M.toast({
                html: 'Önce bir arşiv oluşturmalısınız.',
                classes: 'teal darken-2'
            })
        }
        else
        {
            M.toast({
                html: 'Lütfen bir arşiv seçin!',
                classes: 'red'
            })
        }
    }

    function __archive(__, obj)
    {
        if (obj.status == 'removed')
        {
            $('[data-pin-uuid=' + __.attr('data-pin-uuid') + ']').removeClass('on')

            M.toast({ html: 'Arşivden Çıkarıldı', classes: 'red darken-2' })
        }
        else if (obj.status == 'pinned')
        {
            $('[data-pin-uuid=' + __.attr('data-pin-uuid') + ']').addClass('on')

            var toastHTML = $('<div />', {
                'html': [
                    $('<span />', {
                        'html': 'Arşivlendi',
                        'class': 'white-text'
                    }),
                    $('<a />', {
                        'href': '#',
                        'class': 'btn-flat toast-action json',
                        'html': 'Geri Al',
                        'data-undo': 'true',
                        'data-href': '{{ route('pin', 'remove') }}',
                        'data-method': 'post',
                        'data-callback': '__archive',
                        'data-id': __.data('id'),
                        'data-type': __.data('type'),
                        'data-index': __.data('index'),
                        'data-pin-uuid': __.data('pin-uuid'),
                        'data-include': 'archive_id'
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
