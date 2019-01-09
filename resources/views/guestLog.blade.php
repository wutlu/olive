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
            'text' => 'Ziyaretçi Logları'
        ]
    ]
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

                        item.find('[data-name=user-name]').html(o.user ? o.user.name : '').addClass(o.user ? '' : 'hide')
                        item.find('[data-name=ip]').html(o.ip_address)
                        item.find('[data-name=ping]').html('[' + o.ping + ']')
                        item.find('[data-name=device]').html(o.device).addClass(o.device ? '' : 'hide')
                        item.find('[data-name=robot]').html(o.robot).addClass(o.robot ? '' : 'hide')
                        item.find('[data-name=page]').html(o.page).attr('href', o.page)
                        item.find('[data-name=os]').html(o.os.name + ( o.os.version ? ' (' + o.os.version + ') ' : '' ))
                        item.find('[data-name=browser]').html(o.browser.name + ( o.browser.version ? ' (' + o.browser.version + ') ' : '' ))
                        item.find('[data-name=referer]').html(o.referer).attr('href', o.referer).addClass(o.referer ? '' : 'hide')

                        var type = '';

                        if (o.is_desktop)
                        {
                        	type = 'Masaüstü';
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

            $('#home-loader').hide()

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
        background-image: url('{{ asset('img/olive-logo-opacity.svg') }}');
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
    <div class="card">
        <div class="card-content">
            <span class="card-title mb-0">Ziyaretçi Logları</span>
            <span class="grey-text" data-name="total">0</span>
        </div>
        <ul
            id="console"
            class="collection black load hide"
            data-href="{{ route('admin.session.logs') }}"
            data-callback="__log"
            data-method="post">
            <li class="collection-item hide">
                <div class="d-flex justify-content-between">
                	<span>
	                    <span data-name="ip" class="red-text" style="padding: .1rem;"></span>
	                    <span data-name="ping" class="red-text" style="padding: .1rem;"></span>
	                    <span data-name="user-name" class="white-text" style="padding: .1rem;"></span>
		                <div>
		                    <a href="#" class="d-table grey-text" style="padding: .1rem;" target="_blank" data-name="page"></a>
		                    <a href="#" class="d-table grey-text" style="padding: .1rem;" target="_blank" data-name="referer"></a>
		                </div>
	                </span>
	                <span class="d-flex flex-column justify-content-end">
	                    <span class="right-align" data-name="robot" style="padding: .1rem;"></span>
	                    <span class="right-align" data-name="os" style="padding: .1rem;"></span>
	                    <span class="right-align" data-name="browser" style="padding: .1rem;"></span>
	                    <span class="right-align" data-name="device" style="padding: .1rem;"></span>
	                    <span class="right-align" data-name="type" style="padding: .1rem;"></span>
	                </span>
                </div>
            </li>
        </ul>
    </div>

    @component('components.loader')
        @slot('color', 'cyan')
        @slot('id', 'home-loader')
    @endcomponent
@endsection
