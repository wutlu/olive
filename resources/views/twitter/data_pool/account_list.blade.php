@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Veri Havuzu'
        ],
        [
            'text' => 'Twitter'
        ],
        [
            'text' => 'Kullanıcı Havuzu'
        ]
    ],
    'dock' => true
])

@push('local.styles')
	p { margin: 0; }
@endpush

@section('content')
    <div class="card">
        <div class="card-content">
            <span class="card-title">Kullanıcı Havuzu</span>
            <p class="grey-text" data-name="count"></p>
        </div>
        <div class="card-content orange lighten-4">
        	<p>Anlık olarak Twitter'dan belirli kriterlerle veri alıyoruz.</p>
        	<p>İlgilendiğiniz kullanıcıları belirterek veri toplama sonrasında yüksek analiz sonuçları elde edebilirsiniz.</p>
        	<p>Bu ayarlar bulunduğunuz organizasyon için geçerlidir.</p>
            <p>Organizasyona dahil tüm kullanıcıların kullanıcı havuzu ortaktır.</p>
        	<p>Elde edilen veriler tüm veri.zone kullanıcıları tarafından ortak bir veritabanı üzerinden analize açık olacaktır.</p>
            <p>Twitter üzerinden veri elde etmek için, bağlandığınız Twitter hesabınız kullanılacaktır.</p>
        </div>
        <div class="collection load"
             id="collections"
             data-href="{{ route('twitter.account.list') }}"
             data-callback="__collections"
             data-method="post"
             data-nothing>
            <div class="collection-item nothing d-none">
                <div class="not-found">
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">wb_sunny</i>
                    <p>Kullanıcı Yok</p>
                </div>
            </div>
            <a href="#" class="collection-item model d-none waves-effect" data-trigger="delete">
                <span class="align-self-center">
                    <p data-name="screen-name"></p>
                    <p data-name="id" class="grey-text"></p>
                    <p data-name="reasons"></p>
                </span>
                <small class="badge ml-auto right-align">
                	<i class="material-icons" data-name="status">linear_scale</i>
                </small>
            </a>
        </div>
        <div class="card-content">
            <form
                id="collection-form"
                method="put"
                action="{{ route('twitter.account.create') }}"
                data-callback="__create"
                class="json">
                <div class="input-field">
                    <input id="screen_name" name="screen_name" type="text" class="validate" />
                    <label for="screen_name">Twitter Kullanıcı Adı veya ID</label>
                    <span class="helper-text">Örnek: "rt_erdogan, bigverizone"</span>
                </div>
            </form>
        </div>
        <div class="card-content grey lighten-4">
		    <p class="d-flex">
		    	<i class="material-icons red-text" style="margin: 0 1rem 0 0;">timer</i> Havuzda Değil
		    </p>
		    <p class="d-flex">
		    	<i class="material-icons green-text" style="margin: 0 1rem 0 0;">linear_scale</i> Havuzda
		    </p>
	    </div>
    </div>

    @component('components.loader')
        @slot('color', 'purple')
        @slot('id', 'home-loader')
    @endcomponent
@endsection

@section('dock')
	@include('twitter.data_pool._menu', [ 'active' => 'accounts' ])
@endsection

@push('local.scripts')
    $(document).on('click', '[data-trigger=delete]', function() {
        var __ = $(this);

        var mdl = modal({
                'id': 'delete',
                'body': 'Bu kaydı silmek istiyor musunuz?',
                'size': 'modal-small',
                'title': 'Sil',
                'options': {}
            });

            mdl.find('.modal-footer')
               .html([
                    $('<a />', {
                        'href': '#',
                        'class': 'modal-close waves-effect btn-flat',
                        'html': buttons.cancel
                    }),
                    $('<span />', {
                        'html': ' '
                    }),
                    $('<a />', {
                        'href': '#',
                        'class': 'waves-effect btn red json',
                        'html': buttons.ok,
                        'data-href': '{{ route('twitter.account.delete') }}',
                        'data-id': __.data('id'),
                        'data-method': 'delete',
                        'data-callback': '__delete'
                    })
               ])
    })

    function __delete(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-delete').modal('close')
            $('[data-id=' + obj.data.id + ']').remove()
        }
    }

    var collection_timer;

    function __create(__, obj)
    {
        if (obj.status == 'ok')
        {
            __.find('#screen_name').val('')

            window.clearTimeout(collection_timer)

            vzAjax($('#collections'))

            collection_timer = window.setTimeout(function() {
                vzAjax($('#collections'))
            }, 10000)
        }
    }


    function __collections(__, obj)
    {
        var ul = $('#collections');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('d-none')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var selector = $('[data-id=' + o.id + ']'),

                        item = selector.length ? selector : item_model.clone();
                        item.removeClass('model d-none')
                            .addClass('_tmp d-flex')
                            .attr('data-id', o.id)

                        item.find('[data-name=screen-name]').html(o.screen_name)
                        item.find('[data-name=id]').html(o.user_id)
                        item.find('[data-name=reasons]')
                        	.html(o.reasons ? o.reasons : '-')
                        	.removeClass('green-text red-text')
                        	.addClass(o.reasons ? 'red-text' : 'green-text')

                        item.find('[data-name=status]')
                        	.html(o.status ? 'linear_scale' : 'timer')
                        	.removeClass('green-text red-text')
                        	.addClass(o.status ? 'green-text' : 'red-text')

                        if (!selector.length)
                        {
                            item.appendTo(ul)
                        }
                })

                $('[data-tooltip]').tooltip()
            }

            $('#home-loader').hide()

            $('[data-name=count]').html(obj.hits.length + '/{{ $user->organisation->twitter_follow_limit_user }}')
        }

        window.clearTimeout(collection_timer)

        collection_timer = window.setTimeout(function() {
            vzAjax($('#collections'))
        }, 10000)
    }
@endpush
