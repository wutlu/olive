@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Kullanıcılar'
        ]
    ]
])

@push('local.scripts')
    function __users(__, obj)
    {
        var ul = $('#users');
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
                        item.find('[data-name=email]').html(o.email)
                        item.find('[data-name=avatar]').attr('src', o.avatar ? '{{ asset('/') }}' + o.avatar : '{{ asset('img/people.svg') }}')
                        item.find('[data-name=verified]').html(o.verified ? '✓' : '✕').addClass(o.verified ? 'green-text' : 'red-text')

                        item.appendTo(ul)
                })
            }

            $('#home-loader').hide()
        }
    }

    function __go_user(__, obj)
    {
        if (obj.status == 'ok')
        {
            location.href = obj.route;
        }
    }
@endpush

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/card-header.jpg') }}" alt="Kullanıcılar" />
            <span class="card-title">Kullanıcılar</span>
        </div>
        <nav class="grey darken-4">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#users"
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                    <i class="material-icons">close</i>
                </div>
            </div>
        </nav>
        <div class="collection load json-clear" 
             id="users"
             data-href="{{ route('admin.user.list.json') }}"
             data-method="post"
             data-skip="0"
             data-take="5"
             data-include="string"
             data-more-button="#users-more_button"
             data-callback="__users"
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
                data-name="admin.user"
                data-callback="__go_user">
                <img alt="Avatar" data-name="avatar" style="width: 48px; margin: 0 1rem 0 0;" />
                <span class="align-self-center">
                    <p data-name="name"></p>
                    <p data-name="email" class="grey-text"></p>
                </span>
                <small data-name="verified" class="badge ml-auto"></small>
            </a>
        </div>
    </div>

    @component('components.loader')
        @slot('color', 'purple')
        @slot('id', 'home-loader')
    @endcomponent

    <div class="center-align">
        <button class="btn-flat waves-effect d-none json"
                id="users-more_button"
                type="button"
                data-json-target="#users">Daha Fazla</button>
    </div>
@endsection
