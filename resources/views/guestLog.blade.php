@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Sistem İzleme'
        ],
        [
            'text' => '🐞 Ziyaretçi Logları'
        ]
    ],
    'footer_hide' => true,
    'wide' => true
])

@push('local.scripts')
    var logTimer;

    function __log(__, obj)
    {
        if (obj.status == 'ok')
        {
            var collection = __,
                model = collection.children('tr.hide');

            collection.children('.collection-item:not(.hide)').remove()

            $('[data-name=total]').html(obj.total)

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = $('[data-pid=' + o.id + ']');
                        item = item.length ? item : model.clone();

                        item.removeClass('hide').attr('data-pid', o.id)

                        item.find('[data-name=ip]').html(o.ip_address)
                        item.find('[data-name=location]').html(o.location.city + ' / ' + o.location.country)
                        item.find('[data-name=ping]').html(o.ping)
                        item.find('[data-name=updated-at]').html(o.updated_at)

                        var type = '-';

                        if      (o.is_desktop) type = 'PC'        ;
                        else if (o.is_phone  ) type = 'Telefon'   ;
                        else if (o.is_tablet ) type = 'Tablet'    ;
                        else if (o.is_mobile ) type = 'Mobil'     ;

                        item.find('[data-name=type]').html(type).addClass(type ? '' : 'hide')
                        item.find('[data-name=os]').html(o.os.name + ( o.os.version ? ' (' + o.os.version + ') ' : '' ))
                        item.find('[data-name=browser]').html(o.browser.name + ( o.browser.version ? ' (' + o.browser.version + ') ' : '' ))

                        if (o.user)
                        {
                            item.find('[data-name=user]').html('(' + o.user.name + ')').attr('href', '/admin/kullanici-yonetimi/kullanici/' + o.user.id).removeClass('hide')
                        }

                        if (o.device)
                        {
                            item.find('[data-name=device]').html(o.device)
                        }

                        if (o.robot)
                        {
                            item.find('[data-name=robot]').html(o.robot).removeClass(o.robot ? '' : 'hide')
                        }

                        item.find('[data-name=referer]').attr('href', o.referer ? o.referer : '#').removeClass('greyscale').addClass(o.referer ? '' : 'greyscale')
                        item.find('[data-name=page]').attr('href', o.page ? o.page : '#').removeClass('greyscale').addClass(o.page ? '' : 'greyscale')


                        item.prependTo(collection)
                })
            }

            collection.removeClass('hide')
        }

        window.clearTimeout(logTimer)

        logTimer = window.setTimeout(function() {
            vzAjax(collection)
        }, 4000)
    }
@endpush

@section('content')
    <div class="card mb-1">
        <div class="card-content">
            <span class="card-title">Ziyaretçi Logları (<span data-name="total">0</span>)</span>
        </div>
        <table class="highlight">
            <thead class="grey darken-2 white-text">
                <tr>
                    <th>IP</th>
                    <th>Ping</th>
                    <th>Donanım</th>
                    <th>Cihaz</th>
                    <th>Sayfa</th>
                    <th>Referer</th>
                </tr>
            </thead>
            <tbody
                class="load"
                data-href="{{ route('admin.session.logs') }}"
                data-callback="__log"
                data-loader="#home-loader"
                data-method="post">
                <tr class="hide">
                    <th>
                        <span class="d-table">
                            <span data-name="ip"></span>
                            <a href="#" data-name="user"></a>
                        </span>
                        <span class="d-table" data-name="location"></span>
                    </th>
                    <th>
                        <span class="d-table" data-name="ping"></span>
                        <span class="d-table" data-name="updated-at"></span>
                    </th>
                    <th>
                        <span class="d-table">
                            <span data-name="type"></span>
                            <span data-name="robot" class="hide"></span>
                        </span>
                        <span data-name="os"></span>
                        <span data-name="browser"></span>
                    </th>
                    <th data-name="device">-</th>
                    <th>
                        <a target="_blank" href="#" data-name="page">
                            <i class="material-icons">language</i>
                        </a>
                    </th>
                    <th>
                        <a target="_blank" href="#" data-name="referer">
                            <i class="material-icons">language</i>
                        </a>
                    </th>
                </tr>
            </tbody>
        </table>
    </div>


    <div class="card">
        <div class="card-content">
            <span class="card-title">Kullanıcı Logları</span>
        </div>
        <nav class="nav-half mb-0">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#activities"
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                </div>
            </div>
        </nav>
        <div class="card-content">
            <ul class="collapsible load json-clear" 
                id="activities"
                data-href="{{ route('admin.session.activities') }}"
                data-include="string"
                data-skip="0"
                data-take="15"
                data-more-button="#activities-more_button"
                data-callback="__activities"
                data-method="post"
                data-loader="#activity-loader"
                data-nothing>
                <li class="nothing hide">
                    @component('components.nothing')
                        @slot('cloud_class', 'white-text')
                    @endcomponent
                </li>
                <li class="model hide hoverable">
                    <div class="collapsible-header">
                        <i class="material-icons" data-name="icon"></i>
                        <span>
                            <p data-name="name" class="mb-0 grey-text"></p>
                            <p class="mb-0"></p>
                            <time class="timeago grey-text"></time>
                        </span>
                        <i class="material-icons arrow">keyboard_arrow_down</i>
                    </div>
                    <div class="collapsible-body">
                        <span></span>
                    </div>
                </li>
            </ul>
        </div>

        @component('components.loader')
            @slot('color', 'blue-grey')
            @slot('class', 'card-loader-unstyled')
            @slot('id', 'activity-loader')
        @endcomponent
    </div>

    <div class="center-align">
        <a
            class="more hide json"
            id="activities-more_button"
            href="#"
            data-json-target="ul#activities">Daha Fazla</a>
    </div>
@endsection

@push('local.scripts')
    function __activities(__, obj)
    {
        var item_model = __.children('.model');

        if (obj.status == 'ok')
        {
            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp').attr('data-id', o.id)

                        item.find('.collapsible-header > span > p').html(o.title)
                        item.find('.collapsible-header > span > time').attr('data-time', o.updated_at).html(o.updated_at)
                        item.find('.collapsible-header > [data-name=icon]').html(o.icon)
                        item.find('.collapsible-body > span').html(o.markdown)
                        item.find('[data-name=name]').html(o.user.email)

                        if (o.markdown_color)
                        {
                            item.find('.collapsible-body').css({ 'background-color': o.markdown_color })
                        }

                        if (o.button_text)
                        {
                            var button = $('<a />', {
                                'class': o.button_class,
                                'html': o.button_text,
                                'href': o.button_action
                            });

                            item.find('.collapsible-body').children('span').append(button)
                        }

                        item.appendTo(__)
                })
            }

            $('[data-name=count]').html(obj.total)
        }
    }
@endpush