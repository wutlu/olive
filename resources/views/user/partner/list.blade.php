@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Partner'
        ],
        [
            'text' => 'Kullanıcılar'
        ]
    ],
    'footer_hide' => true,
    'dock' => true
])

@section('dock')
    @include('user.partner._menu', [ 'active' => 'list' ])
@endsection

@push('local.scripts')
    function __collection(__, obj)
    {
        var item_model = __.children('.model');

        if (obj.status == 'ok')
        {
            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp d-flex').attr('data-id', o.id)
                        item.attr('href', '{{ route('partner.user') }}/' + o.id)

                        item.find('[data-name=name]').html(o.name)
                        item.find('[data-name=email]').html(o.email)
                        item.find('[data-name=avatar]').attr('src', o.avatar ? '{{ asset('/') }}' + o.avatar : '{{ asset('img/icons/people.svg') }}')
                        item.find('[data-name=verified]').html(o.verified ? 'Doğrulandı!' : 'Doğrulanmadı!').addClass(o.verified ? 'green-text' : 'red-text')
                        item.find('[data-name=organisation-time]')
                            .html(o.organisation ? o.organisation.end_date : 'Organizasyon Yok')
                            .addClass(o.organisation ? (o.organisation.status == true ? 'green-text' : 'red-text') : 'red-text')

                        item.appendTo(__)
                })
            }

            $('[data-name=count]').html(obj.total)
        }
    }
@endpush

@section('wildcard')
    <div class="card wild-background">
        <div class="container">
            <span class="wildcard-title">
                Partner Kullanıcıları @if ($user->sub_partner_percent) <small class="grey-text">Alt Partner</small> @endif
            </span>
        </div>
    </div>
@endsection

@section('action-bar')
    <a href="{{ route('partner.user') }}" class="btn-floating btn-large halfway-fab waves-effect white">
        <i class="material-icons grey-text text-darken-2">add</i>
    </a>
@endsection

@section('content')
    <input type="hidden" value="{{ $user->id }}" name="id" />

    <div class="card">
        <div class="card-content">
            <span>
                <span class="grey-text d-block">Kullanıcı Sayısı</span>
                <span class="card-title" data-name="count">0</span>
            </span>
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
                </div>
            </div>
        </nav>
        <div class="collection collection-unstyled load json-clear" 
             id="users"
             data-href="{{ route('partner.user.list.json') }}"
             data-method="post"
             data-skip="0"
             data-take="5"
             data-include="string,id"
             data-more-button="#users-more_button"
             data-callback="__collection"
             data-loader="#home-loader"
             data-nothing>
            <div class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </div>
            <a href="#" class="collection-item justify-content-between avatar model hide">
                <img alt="Avatar" data-name="avatar" class="circle" />
                <span>
                    <span data-name="name" class="d-block"></span>
                    <span data-name="email" class="grey-text"></span>
                </span>
                <span class="right-align">
                    <small class="d-block" data-name="verified"></small>
                    <i>
                        <small data-name="organisation-time"></small>
                    </i>
                </span>
            </a>
        </div>

        @component('components.loader')
            @slot('color', 'blue-grey')
            @slot('id', 'home-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>

    <a href="#"
       class="more hide json"
       id="users-more_button"
       data-json-target="#users">Daha Fazla</a>
@endsection
