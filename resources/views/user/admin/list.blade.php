@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => '🐞 Kullanıcılar'
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
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp d-flex').attr('data-id', o.id)

                        item.find('[data-name=name]').html(o.name)
                        item.find('[data-name=email]').html(o.email)
                        item.find('[data-name=avatar]').attr('src', o.avatar ? '{{ asset('/') }}' + o.avatar : '{{ asset('img/icons/people.png') }}')
                        item.find('[data-name=verified]').html(o.verified ? 'Doğrulandı!' : 'Doğrulanmadı!').addClass(o.verified ? 'green-text' : 'red-text')

                        item.appendTo(ul)
                })
            }
        }

        $('#home-loader').hide()
    }
@endpush

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Kullanıcılar</span>
        </div>
        <nav class="nav-half">
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
            <div class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </div>
            <a
                href="#"
                class="collection-item avatar model hide waves-effect json"
                data-href="{{ route('route.generate.id') }}"
                data-method="post"
                data-name="admin.user"
                data-callback="__go">
                <img alt="Avatar" data-name="avatar" class="circle" />
                <span class="align-self-center">
                    <p data-name="name"></p>
                    <p data-name="email" class="grey-text"></p>
                </span>
                <span data-name="verified" class="ml-auto"></span>
            </a>
        </div>
    </div>

    @component('components.loader')
        @slot('color', 'cyan')
        @slot('id', 'home-loader')
    @endcomponent

    <div class="center-align">
        <button class="btn-flat waves-effect hide json"
                id="users-more_button"
                type="button"
                data-json-target="#users">Daha Fazla</button>
    </div>
@endsection
