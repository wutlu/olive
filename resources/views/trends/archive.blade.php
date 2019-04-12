@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Trend Analizi'
        ],
        [
            'text' => 'Trend Arşivi'
        ]
    ],
    'dock' => true
])

@push('local.scripts')
    $('.collapsible').collapsible()
@endpush

@push('local.styles')
    .image-area > img {
        width: 100%;
        display: block;

        border-width: 1px;
        border-style: dashed;
        border-color: #ccc;
    }
@endpush

@section('dock')
    @include('trends._menu', [ 'active' => 'archive' ])
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/html2canvas.min.js?v='.config('system.version')) }}"></script>
@endpush

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Trend Arşivi</span>
        </div>
        <nav class="nav-half">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#archives"
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                </div>
            </div>
        </nav>
        <ul class="collapsible load json-clear" 
            id="archives"
            data-href="{{ route('trend.archive') }}"
            data-skip="0"
            data-take="10"
            data-more-button="#archives-more_button"
            data-callback="__archives"
            data-method="post"
            data-include="string"
            data-loader="#home-loader"
            data-nothing>
            <li class="nothing hide pb-1">
                @component('components.nothing')@endcomponent
            </li>
            <li class="model hide">
                <div class="collapsible-header">
                    <span>
                        <p></p>
                        <time class="timeago grey-text"></time>
                    </span>
                    <i class="material-icons arrow">keyboard_arrow_down</i>
                </div>
                <div class="collapsible-body grey lighten-5">
                    <span>
                        <span data-name="title" class="d-block right-align grey-text"></span>
                        <span data-name="data"></span>
                    </span>
                    <div class="p-1 center-align">
                        <a href="#" class="btn-flat waves-effect" data-trigger="image-save">Resim Kaydet</a>
                    </div>
                </div>
            </li>
        </ul>

        @component('components.loader')
            @slot('color', 'cyan')
            @slot('class', 'card-loader-unstyled')
            @slot('id', 'home-loader')
        @endcomponent
    </div>

    <a href="#"
       class="btn-small white grey-text more hide json"
       id="archives-more_button"
       data-json-target="#archives">Daha Fazla</a>
@endsection

@push('local.scripts')
    $(document).on('click', '[data-trigger=image-save]', function() {
        var __ = $(this);

        html2canvas(document.querySelector('#' + __.data('id')), {
            'logging': false,
            'max-width': '100%'
        }).then(canvas => {
            return modal({
                'id': 'save',
                'body': [
                    $('<div />', {
                        'class': 'teal lighten-2 white-text p-1 mb-1',
                        'html': 'Aşağıdaki resmin üzerine sağ tıklayın ve bilgisayarınıza kaydedin.'
                    }),
                    $('<a />', {
                        'class': 'image-area',
                        'target': '_blank',
                        'href': canvas.toDataURL(),
                        'html': $('<img />', {
                            'src': canvas.toDataURL()
                        })
                    })
                ],
                'title': 'Görüntü Kaydet',
                'size': 'modal-large',
                'options': {},
                'footer': [
                   $('<a />', {
                       'href': '#',
                       'class': 'modal-close waves-effect btn-flat',
                       'html': buttons.ok
                   })
                ]
            })
        })
    })

    function __archives(__, obj)
    {
        var ul = $('#archives');
        var item_model = ul.children('li.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp').attr('data-id', 'list-item-' + o.id)

                        if (!o.organisation_id)
                        {
                            item.addClass('orange lighten-5')
                        }

                        item.find('.collapsible-header > span > p').html(o.title)
                        item.find('.collapsible-header > span > time').attr('data-time', o.updated_at).html(o.updated_at)

                        var list = $('<ul />', {
                            'class': 'collection',
                            'css': {
                                'background-image': 'url(\'{{ asset('img/olive_logo-opacity.svg') }}\')',
                                'background-repeat': 'no-repeat',
                                'background-position': 'center',
                                'background-size': '50%'
                            }
                        });

                        $.each(o.data, function(key, o) {
                            var it = $('<li />', {
                                'class': 'collection-item',
                                'html': [
                                    $('<span />', {
                                        'class': 'rank',
                                        'html': key
                                    }),
                                    $('<span />', {
                                        'html': o.title
                                    })
                                ]
                            })

                            it.appendTo(list)
                        })

                        item.find('[data-name=title]').html(o.title)
                        item.find('[data-name=data]').append(list)

                        item.find('[data-trigger=image-save]').attr('data-id', 'capture-' + o.id)

                        item.find('.collapsible-body > span').attr('id', 'capture-' + o.id)

                        item.appendTo(ul)
                })
            }
        }
    }
@endpush
