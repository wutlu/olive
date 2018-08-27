@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Kelime Havuzu'
        ]
    ]
])

@push('local.scripts')
    function __keywords(__, obj)
    {
        var ul = $('#keywords');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('d-none')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model d-none')
                            .addClass('_tmp d-flex')
                            .attr('data-id', o.id)
                            .attr('data-keyword', o.keyword)

                        item.find('[data-name=keyword]').html(o.keyword)
                        item.find('[data-name=user]').html(o.user.name)
                        item.find('[data-name=status]').html(o.status ? 'SENKRONİZE EDİLDİ' : 'HAZIR').addClass(o.status ? 'green-text' : 'red-text')

                        item.appendTo(ul)
                })
            }

            $('#home-loader').hide()
        }
    }

    $(document).on('click', '[data-trigger=form]', function() {
        var __ = $(this);

        var mdl = modal({
            'id': 'detail',
            'body': $('<form />', {
                'method': __.data('id') ? 'patch' : 'put',
                'action': __.data('id') ? '{{ route('keyword.update') }}' : '{{ route('keyword.create') }}',
                'id': 'form',
                'data-id': __.data('id'),
                'data-callback': __.data('id') ? '__update' : '__create',
                'class': 'json',
                'html': $('<div />', {
                    'class': 'input-field',
                    'html': [
                        $('<input />', {
                            'id': 'keyword',
                            'name': 'keyword',
                            'type': 'text',
                            'class': 'validate',
                            'value': __.data('id') ? __.data('keyword') : '',
                            'data-length': 32
                        }),
                        $('<label />', {
                            'for': 'keyword',
                            'html': 'Kelime'
                        }),
                        $('<span />', {
                            'class': 'helper-text'
                        })
                    ]
                })
            }),
            'size': 'modal-medium',
            'title': __.data('id') ? 'Kelime Güncelle' : 'Kelime Oluştur',
            'options': {
                dismissible: false
            }
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
                   __.data('id') ? $('<a />', {
                       'href': '#',
                       'class': 'waves-effect waves-red btn-flat red-text json',
                       'data-id': __.data('id'),
                       'data-href': '{{ route('keyword.delete') }}',
                       'data-method': 'delete',
                       'data-callback': '__delete',
                       'html': buttons.remove
                   }) : '',
                   $('<span />', {
                       'html': ' '
                   }),
                   $('<button />', {
                       'type': 'submit',
                       'class': 'waves-effect btn',
                       'html': buttons.ok,
                       'data-submit': 'form#form'
                   })
               ])

        M.updateTextFields()

        $('input[name=keyword]').characterCounter().focus()
    })

    function __update(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-detail').modal('close')

            M.toast({ html: 'Kelime Güncellendi', classes: 'green' })

            var item = $('[data-id=' + obj.data.id + ']');
                item.data('keyword', obj.data.keyword)
                item.find('[data-name=keyword]').html(obj.data.keyword)
        }
    }

    function __delete(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-detail').modal('close')

            M.toast({ html: 'Kelime Silindi', classes: 'green' })

            $('[data-id=' + obj.data.id + ']').remove()

            if (!obj.data.count)
            {
                vzAjax($('#keywords').data('skip', 0).addClass('json-clear'))
            }
        }
    }

    function __create(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-detail').modal('close')

            vzAjax($('#keywords').data('skip', 0).addClass('json-clear'))

            M.toast({ html: 'Kelime Oluşturuldu', classes: 'green' })
        }
    }
@endpush

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/md-s/23.jpg') }}" alt="Kelimeler" />
            <span class="card-title">Kelimeler</span>
            <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-trigger="form">
                <i class="material-icons black-text">add</i>
            </a>
        </div>
        <div class="card-content orange lighten-4">
            <p>- Eklediğiniz/Ekleyeceğiniz kelimeler bulunduğunuz organizasyona tanımlanır ve depolanacak içeriklerin filtrelenmesini sağlar.</p>
            <p>- Filtreleme işleminde veri kirliliğinin en aza indirgenmesi amaçlanır.</p>
            <p>- Elde edilen veriler tüm organizasyonlar için ortak bir havuzda toplanarak, daha geniş kapsamlı analizler çıkartılır.</p>
        </div>
        <nav class="grey darken-4">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#keywords"
                           placeholder="Arayın" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                    <i class="material-icons">close</i>
                </div>
            </div>
        </nav>
        <div class="collection load json-clear"
             id="keywords"
             data-href="{{ route('keyword.list.json') }}"
             data-skip="0"
             data-take="5"
             data-include="string"
             data-more-button="#keywords-more_button"
             data-callback="__keywords"
             data-nothing>
            <div class="collection-item nothing d-none">
                <div class="not-found">
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">wb_sunny</i>
                    <p>Kelime Yok</p>
                </div>
            </div>
            <a
                href="#"
                data-trigger="form"
                class="collection-item model d-none waves-effect">
                <span class="align-self-center">
                    <p data-name="keyword"></p>
                    <p data-name="user" class="grey-text"></p>
                </span>
                <small data-name="status" class="badge ml-auto"></small>
            </a>
        </div>
    </div>

    @component('components.loader')
        @slot('color', 'purple')
        @slot('id', 'home-loader')
    @endcomponent

    <div class="center-align">
        <button class="btn-flat waves-effect d-none json"
                id="keywords-more_button"
                type="button"
                data-json-target="#keywords">Daha Fazla</button>
    </div>
@endsection
