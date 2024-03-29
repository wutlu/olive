@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Arşivler'
        ]
    ],
    'footer_hide' => true
])

@push('local.scripts')
    function __archives(__, obj)
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
                        item.find('[data-trigger=archive-go]').html(o.name).attr('data-id', o.id)
                        item.find('[data-name=created-at]').attr('data-time', o.created_at).html(o.created_at)
                        item.find('[data-name=count]').html(o.items_count + ' içerik')

                        item.appendTo(__)
                })
            }
        }

        $('[data-name=group-count]').html(obj.hits.length + ' / {{ auth()->user()->organisation->archive_limit }}')
    }

    function archive_modal()
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
                                'id': 'name',
                                'name': 'name',
                                'type': 'text',
                                'class': 'validate',
                                'data-length': 32
                            }),
                            $('<label />', {
                                'for': 'name',
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

        mdl.find('input[name=name]').characterCounter().focus()

        M.updateTextFields()

        return mdl;
    }

    $(document).on('click', '[data-trigger=create-pin-group]', function() {
        var mdl = archive_modal();
            mdl.find('.modal-title').html('Arşiv Oluştur')
            mdl.find('form#pin-group-form').data('method', 'put')

            mdl.find('[name=name]').val('')

        $('[data-trigger=delete-pin-group]').removeAttr('data-id').addClass('hide')
    })

    function __get_archive_group(__, obj)
    {
        if (obj.status == 'ok')
        {
            var mdl = archive_modal();
                mdl.find('.modal-title').html('Arşiv Güncelle')
                mdl.find('form#pin-group-form').data('id', obj.data.id)
                                               .data('method', 'patch')
                mdl.find('[name=name]').val(obj.data.name)

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
                vzAjax($('#archives').data('skip', 0).addClass('json-clear'))
            }
            else if (obj.type == 'updated')
            {
                $('#archives').children('[data-id=' + obj.data.id + ']').find('[data-trigger=archive-go]').html(obj.data.name)
            }

            M.toast({
                html: obj.type == 'created' ? 'Arşiv Oluşturuldu' : obj.type == 'updated' ? 'Arşiv Güncellendi' : 'İşlem Gerçekleşti',
                classes: 'green darken-2'
            })
        }
    }

    $(document).on('click', '[data-trigger=delete-pin-group]', function() {
        var mdl = modal({
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
            });
    })

    function __delete_archive_group(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#archives').children('[data-id=' + obj.data.id + ']').remove()

            $('#modal-pin-group-alert').modal('close')

            setTimeout(function() {
                $('#modal-pin-group').modal('close')
            }, 200)

            M.toast({
                html: 'Arşiv Silindi',
                classes: 'red darken-2'
            })

            vzAjax($('#archives').data('skip', 0).addClass('json-clear'))
        }
    }
@endpush

@section('content')
    <div class="card mb-1">
        <div class="card-image">
            <img src="{{ asset('img/md-s/21.jpg') }}" alt="Image" />
            <span class="card-title white-text d-flex">
                <i class="material-icons align-self-center mr-1">archive</i>
                Arşivler
            </span>
            <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-trigger="create-pin-group">
                <i class="material-icons grey-text text-darken-2">add</i>
            </a>
        </div>
        <div class="card-content">
            <span class="d-block grey-text text-darken-2" data-name="group-count"></span>
            <p class="grey-text text-darken-2">İlgilendiğiniz içerikleri daha sonra kullanmak üzere arşivler halinde saklayabilirsiniz.</p>
        </div>
        <nav class="nav-half mb-0">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#archives"
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                </div>
            </div>
        </nav>
        <ul id="archives"
             class="collection load json-clear" 
             data-href="{{ route('pin.groups') }}"
             data-skip="0"
             data-take="5"
             data-include="string"
             data-more-button="#archives-more_button"
             data-callback="__archives"
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
                        data-callback="__get_archive_group"
                        href="#">
                        <i class="material-icons">create</i>        
                    </a>
                </span>
                <span>
                	<a
                        data-trigger="archive-go"
                        data-name="pin.pins"
                        data-href="{{ route('route.generate.id') }}"
                        data-method="post"
                        data-callback="__go"
                        class="json d-table"
                        href="#"></a>
                    <time data-name="created-at" class="timeago grey-text"></time>
                </span>
                <span data-name="count" class="ml-auto"></span>
            </li>
        </ul>

        @component('components.loader')
            @slot('color', 'blue-grey')
            @slot('id', 'home-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>

    <div class="center-align">
        <button class="btn-flat waves-effect hide json"
                id="archives-more_button"
                type="button"
                data-json-target="#archives">Öncekiler</button>
    </div>
@endsection
