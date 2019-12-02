@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Ayarlar'
        ],
        [
            'text' => 'Arama Geçmişi'
        ]
    ],
    'dock' => true
])

@push('local.scripts')
    function __history(__, obj)
    {
        var ul = $('#history');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp').attr('data-id', o.id)

                        item.find('[data-name=query]').html(o.query)
                        item.find('[data-name=created-at]').attr('data-time', o.created_at).html(o.created_at)
                        item.find('[data-trigger=delete]').attr('data-id', o.id)

                        item.appendTo(ul)
                })
            }
        }
    }

    $(document).on('click', '[data-trigger=delete]', function() {
        var mdl = modal({
                'id': 'alert',
                'body': 'Arama geçmişten silinecek?',
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
                        'data-href': '{{ route('settings.search_history') }}',
                        'data-method': 'delete',
                        'data-id': $(this).data('id'),
                        'data-callback': '__delete'
                    })
                ]
            });
    })

    function __delete(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#history').children('[data-id=' + obj.data.id + ']').remove()

            $('#modal-alert').modal('close')

            M.toast({
                html: 'Arama Silindi',
                classes: 'red darken-2'
            })

            if ($('#history').children('[data-id]').length <= 0)
            {
            	vzAjax($('#history').data('skip', 0).addClass('json-clear'))
            }
        }
    }
@endpush

@section('content')
    <div class="card mb-1">
        <div class="card-content">
            <span class="card-title d-flex">
                <i class="material-icons align-self-center mr-1">speaker_notes</i>
                Arama Geçmişi
            </span>
        </div>
        <nav class="nav-half mb-0">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#history"
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                </div>
            </div>
        </nav>
        <ul id="history"
             class="collection collection-hoverable load json-clear" 
             data-href="{{ route('settings.search_history') }}"
             data-skip="0"
             data-take="25"
             data-include="string"
             data-more-button="#history-more_button"
             data-callback="__history"
             data-method="post"
             data-loader="#home-loader"
             data-nothing>
            <li class="collection-item nothing hide">
                @component('components.nothing')
                    @slot('size', 'small')
                    @slot('text', 'Henüz hiç arama yapmadınız!')
                @endcomponent
            </li>
            <li data-name="item" class="collection-item model hide">
            	<div class="d-flex justify-content-between">
	                <span class="align-self-center">
	                	<code data-name="query"></code>
	                    <time data-name="created-at" class="timeago grey-text d-table"></time>
	                </span>
	                <a href="#" class="btn-floating btn-flat waves-effect align-self-center" data-trigger="delete">
	                	<i class="material-icons">delete</i>
	                </a>
	            </div>
            </li>
        </ul>

        @component('components.loader')
            @slot('color', 'blue-grey')
            @slot('id', 'home-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>

    <div class="center-align">
        <button class="btn-flat waves-effect hide json"
                id="history-more_button"
                type="button"
                data-json-target="#history">Öncekiler</button>
    </div>
@endsection

@section('dock')
    @include('settings._menu', [ 'active' => 'search_history' ])
@endsection
