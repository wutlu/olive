@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Veri Havuzu',
            'link' => route('data_pool.dashboard')
        ],
        [
            'text' => 'Twitter'
        ],
        [
            'text' => 'Kullanıcı Havuzu'
        ]
    ],
    'dock' => true,
    'footer_hide' => true
])

@section('content')
    <div class="card">
        <div class="card-content">
            <span class="card-title">Kullanıcı Havuzu</span>
            <span data-name="count" class="grey-text text-darken-2">0 / 0</span>
        </div>

        <nav class="nav-half mb-0">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#collections"
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                </div>
            </div>
        </nav>

        <ul class="collection load json-clear loading" 
             id="collections"
             data-href="{{ route('twitter.account.list') }}"
             data-skip="0"
             data-take="10"
             data-include="string"
             data-more-button="#more_button"
             data-callback="__collections"
             data-method="post"
             data-loader="#home-loader"
             data-nothing>
            <li class="collection-item nothing hide">
                @component('components.nothing')
                    @slot('size', 'small')
                @endcomponent
            </li>
            <li class="collection-item model hide">
                <div class="d-flex justify-content-between">
                    <span>
                        <span class="d-table" data-name="screen-name"></span>
                        <a href="#" class="blue-text text-darken-2" data-name="id" target="_blank"></a>
                        <p class="mb-0" data-name="reason"></p>
                    </span>
                    <span class="right-align">
                        <time class="timeago grey-text d-block" data-name="created-at">-</time>
                        <a href="#" data-trigger="delete">
                            <i class="material-icons">delete</i>
                        </a>
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

    <a href="#"
       class="more hide json"
       id="more_button"
       data-json-target="#collections">Daha Fazla</a>
@endsection

@section('dock')
	@include('dataPool._menu', [ 'active' => 'twitter.accounts' ])

    <div class="p-1">
        <label>
            <input name="saver" type="checkbox" value="on" />
            <span>Enter tuşu ile aramadaki kelimeyi kaydet.</span>
        </label>
    </div>
@endsection

@push('local.scripts')
    $(document).on('click', '[data-trigger=delete]', function() {
        return modal({
            'id': 'delete',
            'body': 'Bu kaydı silmek istiyor musunuz?',
            'size': 'modal-small',
            'title': 'Sil',
            'options': {},
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect btn-flat grey-text',
                    'html': keywords.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<a />', {
                    'href': '#',
                    'class': 'waves-effect btn-flat red-text json',
                    'html': keywords.ok,
                    'data-href': '{{ route('twitter.account.delete') }}',
                    'data-id': $(this).closest('li').data('id'),
                    'data-method': 'delete',
                    'data-callback': '__delete'
                })
            ]
        })
    }).on('keyup', 'input[name=string]', function(e) {
        if (e.keyCode == 13 && $('input[name=saver]').is(':checked'))
        {
            vzAjax($('<div />', {
                'data-include': 'string',
                'data-method': 'put',
                'data-href': '{{ route('twitter.account.create') }}'
            }))
        }
    })

    function __delete(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-delete').modal('close')

            $('li[data-id=' + __.data('id') + ']').slideUp()
        }
    }

    function __collections(__, obj)
    {
        var item_model = __.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();

                        item.removeClass('model hide')
                            .addClass('_tmp')
                            .attr('data-id', o.id)
                            .attr('data-name', o.user_id)

                        item.find('[data-name=created-at]').attr('data-time', o.created_at)

                        var link = 'https://twitter.com/intent/user?user_id=' + o.user_id;

                        item.find('[data-name=id]').attr('href', link).html(link)
                        item.find('[data-name=screen-name]').html(o.screen_name ? o.screen_name : '').addClass(o.screen_name ? '' : 'hide')
                        item.find('[data-name=reason]').html(o.reason ? o.reason : '').addClass(o.reason ? 'red-text' : 'hide')

                        item.appendTo(__)
                })
            }

            $('[data-name=count]').html(obj.total + ' / {{ auth()->user()->organisation->data_pool_twitter_user_limit }}')
        }
    }
@endpush
