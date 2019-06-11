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
    'footer_hide' => true
])

@push('local.scripts')
    var logTimer;

    function __log(__, obj)
    {
        if (obj.status == 'ok')
        {
            var collection = $('#console.collection'),
                model = collection.children('.collection-item.hide');

            collection.children('.collection-item:not(.hide)').remove()

            $('[data-name=total]').html(obj.total)

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = model.clone();

                        item.removeClass('hide').attr('data-pid', o.pid)

                        item.find('[data-name=ip]').html(o.ip_address)
                        item.find('[data-name=ping]').html(o.ping + ' hit')
                        item.find('[data-name=page]').html(o.page).attr('href', o.page)
                        item.find('[data-name=os]').html(o.os.name + ( o.os.version ? ' (' + o.os.version + ') ' : '' ))
                        item.find('[data-name=browser]').html(o.browser.name + ( o.browser.version ? ' (' + o.browser.version + ') ' : '' ))
                        item.find('[data-name=location]').html('(' + o.location.city + ' / ' + o.location.country + ')')

                        if (o.user)
                        {
                            item.find('[data-id=user-name]').html(o.user.name).removeClass('hide').data('id', o.user.id)
                        }

                        if (o.device)
                        {
                            item.find('[data-name=device]').html(o.device).removeClass('hide')
                        }

                        if (o.robot)
                        {
                            item.find('[data-name=robot]').html(o.robot).removeClass('hide')
                        }

                        if (o.referer)
                        {
                            item.find('[data-name=referer]').html(o.referer).attr('href', o.referer).removeClass('hide')
                        }

                        var type = '';

                        if (o.is_desktop)
                        {
                        	type = 'PC';
                        }
                        else if (o.is_phone)
                        {
                        	type = 'Telefon';
                        }
                        else if (o.is_tablet)
                        {
                        	type = 'Tablet';
                        }
                        else if (o.is_mobile)
                        {
                        	type = 'Mobil';
                        }

                        item.find('[data-name=type]').html(type).addClass(type ? '' : 'hide')

                        item.prependTo(collection)
                })
            }

            collection.removeClass('hide')

            window.clearTimeout(logTimer)

            logTimer = window.setTimeout(function() {
                vzAjax(collection)
            }, 10000)
        }
    }
@endpush

@push('local.styles')
    #console.collection {
        background-image: url('{{ asset('img/olive_logo-opacity.svg') }}');
        background-repeat: no-repeat;
        background-position: center;
        padding: 1rem 0;
    }
    #console.collection > .collection-item {
        color: rgba(255, 255, 255, .6);
    }
    #console.collection > .collection-item:hover {
        background-color: rgba(255, 255, 255, .2);
        color: #fff;
    }
@endpush

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Ziyaretçi Logları</span>
            <span class="grey-text" data-name="total">0</span>
        </div>
        <ul
            id="console"
            class="collection load hide"
            data-href="{{ route('admin.session.logs') }}"
            data-callback="__log"
            data-loader="#home-loader"
            data-method="post">
            <li class="collection-item hide">
                <div class="d-flex justify-content-between flex-wrap">
                	<span>
                        <span class="d-block">
                            <span data-name="ip" style="padding: .1rem;"></span>
                            <span data-name="location" style="padding: .1rem;"></span>
                        </span>
                        <a
                            href="#"
                            data-id="user-name"
                            class="teal-text hide json"
                            style="padding: .1rem;"
                            data-href="{{ route('route.generate.id') }}"
                            data-method="post"
                            data-name="admin.user"
                            data-callback="__go"></a>
                        <span class="d-block">
                            <a href="#" class="d-table grey-text" style="padding: .1rem;" target="_blank" data-name="page"></a>
                            <a href="#" class="d-table grey-text hide" style="padding: .1rem;" target="_blank" data-name="referer"></a>
                        </span>
                    </span>
                    <span class="d-flex flex-column align-items-end">
                        <span class="hide" data-name="robot" style="padding: .1rem;"></span>
                        <span class="d-block">
                            <span data-name="type" style="padding: .1rem;"></span>
                            <span data-name="os" style="padding: .1rem;"></span>
                        </span>
                        <span data-name="browser" style="padding: .1rem;"></span>
                        <span class="hide" data-name="device" style="padding: .1rem;"></span>
                        <span data-name="ping" style="padding: .1rem;"></span>
	                </span>
                </div>
            </li>
        </ul>
        @component('components.loader')
            @slot('color', 'blue-grey')
            @slot('id', 'home-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>
@endsection
