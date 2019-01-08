@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'E-posta Bülteni'
        ]
    ]
])

@push('local.scripts')
    function __newsletters(__, obj)
    {
        var ul = $('#newsletters');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp d-flex').attr('data-id', o.id)

                        item.find('[data-name=subject]').html(o.subject)
                        item.find('[data-name=status]').html(
                            o.status == 'process' ? 'İşleniyor...' : (o.status == 'ok' ? 'Tamamlandı!' : o.status == 'triggered' ? 'Planlandı...' : 'Henüz Planlanmadı!')
                        ).addClass(o.status == 'process' ? 'orange-text' : (o.status == 'ok' ? 'green-text' : 'grey-text'))
                        item.find('[data-name=updated-at]').html(o.updated_at).data('time', o.updated_at)

                        item.appendTo(ul)
                })
            }

            $('#home-loader').hide()
        }
    }
@endpush

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/card-header.jpg') }}" alt="E-posta Bülteni" />
            <span class="card-title">E-posta Bülteni</span>

            <a href="{{ route('admin.newsletter.form') }}" class="btn-floating btn-large halfway-fab waves-effect white">
                <i class="material-icons grey-text text-darken-2">add</i>
            </a>
        </div>
        <div class="card-content grey lighten-4 grey-text">
            <p>E-posta bültenleri işleme alındıktan sonra tamamlanmadan sonlandırılamaz/güncellenemez/silinemez.</p>
        </div>
        <nav class="grey darken-4">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#newsletters"
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                    <i class="material-icons">close</i>
                </div>
            </div>
        </nav>
        <div class="collection load json-clear" 
             id="newsletters"
             data-href="{{ route('admin.newsletter') }}"
             data-method="post"
             data-skip="0"
             data-take="5"
             data-include="string"
             data-more-button="#newsletters-more_button"
             data-callback="__newsletters"
             data-nothing>
            <div class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </div>
            <a
                href="#"
                class="collection-item model hide waves-effect json justify-content-between"
                data-href="{{ route('route.generate.id') }}"
                data-method="post"
                data-name="admin.newsletter.form"
                data-callback="__go">
                <span>
                    <p data-name="subject" class="mb-0"></p>
                    <p data-name="status" class="mb-0"></p>
                </span>
                <time data-name="updated-at" class="timeago"></time>
            </a>
        </div>
    </div>

    @component('components.loader')
        @slot('color', 'cyan')
        @slot('id', 'home-loader')
    @endcomponent

    <div class="center-align">
        <button class="btn-flat waves-effect hide json"
                id="newsletters-more_button"
                type="button"
                data-json-target="#newsletters">Daha Fazla</button>
    </div>
@endsection
