@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Organizasyonlar'
        ]
    ]
])

@push('local.scripts')
    function __organisations(__, obj)
    {
        var ul = $('#organisations');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('d-none')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model d-none').addClass('_tmp d-flex').attr('data-id', o.id)

                        item.find('[data-name=name]').html(o.name)
                        item.find('[data-name=status]').html(o.status ? '✓' : '✕').addClass(o.status ? 'green-text' : 'red-text')
                        item.find('[data-name=author]').html(o.author.name)
                        item.find('[data-name=avatar]').attr('src', o.author.avatar ? '{{ asset('/') }}' + o.author.avatar : '{{ asset('img/people.svg') }}')

                        item.appendTo(ul)
                })
            }

            $('#home-loader').hide()
        }
    }

    function __go_organisation(__, obj)
    {
        if (obj.status == 'ok')
        {
            location.href = obj.route;
        }
    }
@endpush

@section('content')
    <div class="row">
        <div class="col m4 offset-m8 s6 offset-s6 l2 offset-l10">
            <div class="input-field">
                <select name="status" id="status" class="json json-search" data-json-target="#organisations">
                    <option value="" selected>Tümü</option>
                    <option value="on">Aktif</option>
                    <option value="off">Pasif</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/card-header.jpg') }}" alt="Organizasyonlar" />
            <span class="card-title">Organizasyonlar</span>
        </div>
        <nav class="grey darken-4">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#organisations"
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                    <i class="material-icons">close</i>
                </div>
            </div>
        </nav>
        <div class="collection load json-clear" 
             id="organisations"
             data-href="{{ route('admin.organisation.list.json') }}"
             data-skip="0"
             data-take="5"
             data-include="string,status"
             data-more-button="#organisations-more_button"
             data-callback="__organisations"
             data-nothing>
            <div class="collection-item nothing d-none">
                <div class="not-found">
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">wb_sunny</i>
                </div>
            </div>
            <a
                href="#"
                class="collection-item model d-none waves-effect json"
                data-href="{{ route('route.generate.id') }}"
                data-name="admin.organisation"
                data-callback="__go_organisation">
                <img alt="Avatar" data-name="avatar" style="width: 48px; margin: 0 1rem 0 0;" />
                <span class="align-self-center">
                    <p data-name="name"></p>
                    <p data-name="author" class="grey-text"></p>
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
                id="organisations-more_button"
                type="button"
                data-json-target="#organisations">Daha Fazla</button>
    </div>
@endsection

@push('local.scripts')
    $('select').formSelect()
@endpush
