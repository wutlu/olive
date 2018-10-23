@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Bot Yönetimi'
        ],
        [
            'text' => 'Twitter Ayarları',
            'link' => route('admin.twitter.settings')
        ],
        [
        	'text' => 'Bağlı Hesaplar'
        ]
    ],
    'dock' => true
])

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/card-header.jpg') }}" alt="Bağlı Hesaplar" />
            <span class="card-title">
                Bağlı Hesaplar
                <small class="d-block" data-name="bots-count"></small>
            </span>
        </div>
        <nav class="grey darken-4">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#crawlers"
                           placeholder="Arayın" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                    <i class="material-icons">close</i>
                </div>
            </div>
        </nav>
        <ul class="collection load json-clear"
             id="crawlers"
             data-href="{{ route('crawlers.twitter.accounts.list.json') }}"
             data-skip="0"
             data-take="5"
             data-include="string"
             data-more-button="#crawlers-more_button"
             data-callback="__crawlers"
             data-nothing>
            <li class="collection-item nothing d-none">
                <div class="not-found">
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">wb_sunny</i>
                    <p>Hesap Yok</p>
                </div>
            </li>
            <li class="collection-item avatar model d-none">
                <img alt="avatar" class="circle" data-name="avatar" />
                <span class="align-self-center">
                    <p data-name="name"></p>
                    <p data-name="screen-name" class="grey-text"></p>
                    <time data-name="created-at" class="timeago grey-text"></time>
                </span>
                <small class="badge ml-auto">
                    <i class="material-icons" data-name="status">sentiment_very_satisfied</i>
                </small>
            </li>
        </ul>
    </div>

    @component('components.loader')
        @slot('color', 'purple')
        @slot('id', 'home-loader')
    @endcomponent

    <div class="center-align">
        <button class="btn-flat waves-effect d-none json"
                id="crawlers-more_button"
                type="button"
                data-json-target="#crawlers">Daha Fazla</button>
    </div>
@endsection

@section('dock')
	@include('crawlers.twitter._menu', [ 'active' => 'accounts' ])
@endsection

@push('local.scripts')
    function __crawlers(__, obj)
    {
        var ul = $('#crawlers');
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

                        item.find('[data-name=id]').html('Id: ' + o.id)
                        item.find('[data-name=name]').html(o.name)
                        item.find('[data-name=screen-name]').html(o.screen_name)
                        item.find('[data-name=status]').addClass(o.status ? 'green-text' : 'red-text')
                        item.find('[data-name=avatar]').attr('src', o.avatar)
                        item.find('[data-name=created-at]').attr('data-time', o.created_at)

                        item.appendTo(ul)
                })

                $('[data-tooltip]').tooltip()
            }

            $('#home-loader').hide()
        }
    }
@endpush
