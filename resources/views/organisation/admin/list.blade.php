@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => '🐞 Organizasyonlar'
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
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp d-flex').attr('data-id', o.id)

                        item.find('[data-name=name]').html(o.name)
                        item.find('[data-name=status]').html(o.status ? 'Ödeme Alındı!' : 'Ödeme Alınmadı!').addClass(o.status ? 'green-text' : 'red-text')
                        item.find('[data-name=author]').html(o.author.name)
                        item.find('[data-name=avatar]').attr('src', o.author.avatar ? '{{ asset('/') }}' + o.author.avatar : '{{ asset('img/icons/people.png') }}')

                        item.appendTo(ul)
                })
            }

            $('[data-name=count]').html(obj.total)
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
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Organizasyonlar</span>
            <span data-name="count" class="grey-text text-darken-2">0</span>
        </div>
        <nav class="nav-half">
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
             data-method="post"
             data-skip="0"
             data-take="10"
             data-include="string,status"
             data-more-button="#organisations-more_button"
             data-callback="__organisations"
             data-loader="#home-loader"
             data-nothing>
            <div class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </div>
            <a
                href="#"
                class="collection-item avatar model hide waves-effect json"
                data-href="{{ route('route.generate.id') }}"
                data-method="post"
                data-name="admin.organisation"
                data-callback="__go">
                <img alt="Avatar" data-name="avatar" class="circle" />
                <span class="align-self-center">
                    <p data-name="name"></p>
                    <p data-name="author" class="grey-text"></p>
                </span>
                <span data-name="status" class="ml-auto"></span>
            </a>
        </div>

        @component('components.loader')
            @slot('color', 'cyan')
            @slot('id', 'home-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>

    <a href="#"
       class="btn-small white grey-text more hide json"
       id="organisations-more_button"
       data-json-target="#organisations">Daha Fazla</a>
@endsection

@push('local.scripts')
    $('select').formSelect()
@endpush
