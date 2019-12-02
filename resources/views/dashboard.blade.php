@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'report_menu' => true
])

@push('local.scripts')
    $('.carousel.carousel-slider').carousel({
        fullWidth: true,
        indicators: true
    })

    $('.carousel-news').owlCarousel({
        responsiveClass: true,
        autoWidth: true,
        dotClass: 'hide',
        navText: [
            '<div class="nav-btn prev-slide d-flex"><i class="material-icons align-self-center">keyboard_arrow_left</i></div>',
            '<div class="nav-btn next-slide d-flex"><i class="material-icons align-self-center">keyboard_arrow_right</i></div>'
        ],
        nav: true,
        loop: true,
        responsive: {
            0: { items: 1 },
            800: { items: 2 },
            1400: { items: 3 }
        },
        autoplay: true,
        autoplayTimeout: 2000,
        autoplayHoverPause: true,
        lazyLoad: true
    })
@endpush

@push('local.styles')
    .carousel-slider {
        margin: 0;
        text-align: center;
    }
    .carousel-slider h1 {
        margin: 1rem 0;
        font-size: 24px;
    }
    @media (max-width: 1024px) {
        .carousel-slider {
            margin: 1rem 0 0;
        }
    }

    .carousel-news {
        display: none;
        height: 92px;
        overflow: hidden;

        -webkit-box-shadow: 0 0 24px 0 rgba(0, 0, 0, .1);
                box-shadow: 0 0 24px 0 rgba(0, 0, 0, .1);
    }
    .carousel-news [data-name=item] > img {
        width: auto;
        height: 64px;
    }
    .carousel-news [data-name=item] > span {
        max-height: 64px;
        max-width: 200px;
        padding: 0 1rem;
        overflow: hidden;
    }

    .indicators > .indicator-item {
        background-color: #666 !important;
        width: 14px !important;
        height: 14px !important;
    }
    .indicators > .indicator-item.active {
        background-color: #333 !important;
    }

    .organisation-card > .card-content-image {
        padding: 72px 1rem 1rem;

        -webkit-box-shadow: inset 0 0 0 256px rgba(0, 0, 0, .4);
                box-shadow: inset 0 0 0 256px rgba(0, 0, 0, .4);
    }
    .organisation-card > .card-content-image .card-title {
        text-shadow: 1px 1px 1px rgba(0, 0, 0, .4);
    }
@endpush

@section('wildcard')
    @if (count($news))
        <div class="carousel-news owl-carousel">
            @foreach ($news as $item)
                <a href="{{ route('search.dashboard').'?q="'.$item->data->title.'"' }}" class="d-flex" data-name="item">
                    @isset($item->data->image)
                        <!--<img class="align-self-center" alt="{{ $item->data->title }}" src="{{ $item->data->image }}" />-->
                    @endisset
                    <span class="align-self-center">{{ $item->data->title }}</span>
                </a>
            @endforeach
        </div>
    @endif
@endsection

@section('content')
    @if (count($carousels))
        <div class="carousel carousel-slider">
            @php
            $i = 0;
            @endphp
            @foreach ($carousels as $carousel)
                <div class="{{ implode(' ', [ 'carousel-item', $i == 0 ? 'active' : '', '' ]) }}">
                    <h1>{{ $carousel->title }}</h1>
                    <div class="markdown">
                        {!! $carousel->markdown() !!}
                    </div>
                    <div class="{{ implode(' ', [ 'anim', $carousel->pattern ]) }}"></div>

                    @if ($carousel->button_text)
                        <a href="{{ $carousel->button_action }}" class="btn-flat waves-effect mt-1 teal-text text-darken-2">
                            {{ $carousel->button_text }}
                        </a>
                    @endif
                </div>
                @php
                $i++;
                @endphp
            @endforeach
        </div>
    @endif
    @if (count($modals))
        @foreach ($modals as $carousel)
            @if ($carousel->modal)
                @push('local.scripts')
                    modal({
                        'id': 'carousel-{{ $carousel->id }}',
                        'body': $('<div />', {
                            'class': 'markdown',
                            'html': '{!! str_replace(PHP_EOL, '', $carousel->markdown()) !!}'
                        }),
                        'size': 'modal-large',
                        'title': '{{ $carousel->title }}',
                        'options': {},
                        'footer': [
                            $('<a />', {
                                'href': '#',
                                'class': 'modal-close waves-effect btn-flat',
                                'html': keywords.ok
                            })
                        ]
                    })
                @endpush
            @endif
        @endforeach
    @endif

    <div class="row mb-1">
        <div class="col s12">
            <div class="fast-menu">
                @foreach ([
                    [
                        'route' => route('compare.dashboard'),
                        'icon' => 'pie_chart',
                        'name' => 'Veri Kıyasla',
                        'key' => 'pool'
                    ],
                    [
                        'route' => route('trend.live'),
                        'icon' => 'trending_up',
                        'name' => 'Canlı Trend',
                        'key' => 'trend'
                    ],
                    [
                        'route' => route('search.dashboard'),
                        'icon' => 'youtube_searched_for',
                        'name' => 'Arama Motoru',
                        'key' => 'search'
                    ],
                    [
                        'route' => route('realtime.stream'),
                        'icon' => 'watch_later',
                        'name' => 'Gerçek Zamanlı Akış',
                        'key' => 'stream'
                    ],
                    [
                        'route' => route('alarm.dashboard'),
                        'icon' => 'access_alarm',
                        'name' => 'Alarmlar',
                        'key' => 'alarm'
                    ],
                    [
                        'route' => route('pin.groups'),
                        'icon' => 'fiber_pin',
                        'name' => 'Pin Grupları',
                        'key' => 'pin_group'
                    ],
                    [
                        'route' => route('data_pool.dashboard'),
                        'icon' => 'hearing',
                        'name' => 'Veri Havuzu',
                        'key' => 'pool'
                    ],
                    [
                        'route' => route('borsa.main'),
                        'icon' => 'money',
                        'name' => 'Kalabalığın Düşüncesi (Borsa)',
                        'key' => 'borsa'
                    ],
                    [
                        'route' => route('trend.archive'),
                        'icon' => 'archive',
                        'name' => 'Trend Arşivi',
                        'key' => 'archive'
                    ],
                    [
                        'route' => route('trend.popular'),
                        'icon' => 'people',
                        'name' => 'Popüler Kaynaklar',
                        'key' => 'popular_sources'
                    ],
                ] as $key => $item)
                    <a href="{{ $item['route'] }}" id="fast_menu-module-{{ $item['key'] }}">
                        <i class="material-icons">{{ $item['icon'] }}</i>
                        <span class="d-block">{!! $item['name'] !!}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="col s12 xl5">
            @if (@auth()->user()->organisation_id)
                @if (!auth()->user()->intro('search.module') && auth()->user()->term_version)
                    <div class="tap-target red white-text" data-target="search-trigger">
                        <div class="tap-target-content">
                            <h5>Pratiklik Kazanın</h5>
                            <p>Modüller arasında daha hızlı gezinebilirsiniz.</p>
                        </div>
                    </div>
                    @push('local.scripts')
                        $('[data-target=search-trigger]').tapTarget({
                            'onClose': function() {
                                vzAjax($('<div />', {
                                    'class': 'json',
                                    'data-method': 'post',
                                    'data-href': '{{ route('intro', 'search.module') }}'
                                }))
                            }
                        });

                        $('[data-target=search-trigger]').tapTarget('open');
                    @endpush
                @endif
                <div class="card organisation-card mb-1">
                    <div class="card-content card-content-image d-flex justify-content-between" style="background-image: url({{ $photo['img'] }});">
                        <span class="card-title white-text align-self-center" data-name="organisation-name">-</span>
                        <a href="{{ route('settings.organisation') }}" class="btn-floating white waves-effect align-self-center">
                            <i class="material-icons grey-text text-darken-2">settings</i>
                        </a>
                    </div>
                    <div class="card-content">
                        <p class="grey-text mb-0" data-name="organisation-capacity">0 / 0</p>
                        <p class="grey-text mb-0" data-name="organisation-status">-</p>
                    </div>
                    <ul class="collection collection-unstyled load hide"
                         id="collections"
                         data-href="{{ route('dashboard.organisation') }}"
                         data-callback="__organisation"
                         data-method="post"
                         data-loader="#organisation-loader">
                        <li class="collection-item avatar hide model user-status">
                            <img alt="" class="circle align-self-center" data-name="avatar" />
                            <span class="d-block align-self-center">
                                <span class="title" data-name="name"></span> - <span class="grey-text" data-name="e-mail"></span>
                                <p class="grey-text d-block" data-name="title"></p>
                            </span>
                        </li>
                    </ul>
                    @component('components.loader')
                        @slot('color', 'blue-grey')
                        @slot('id', 'organisation-loader')
                        @slot('class', 'card-loader-unstyled')
                    @endcomponent
                </div>

                @php
                    $hints = [
                        '<span class="grey darken-2 white-text">#tarihibuluşmaFOXta && !external.type:retweet</span> gibi bir sorgu ile retweetleri arama dışında tutabilirsiniz.',
                        'Veri havuzuna ekleyeceğiniz kullanıcıların silindi bilgilerini de alabilirsiniz.',
                        'Trend algoritması her dakika güncellenir. Son 10 dakikalık verilere göre yenilenir.',
                    ];

                    shuffle($hints);
                @endphp

                <div class="pt-1 pb-1">
                    <div class="grey-text text-darken-2 mt-1">
                        @component('components.alert')
                            @slot('icon', 'lightbulb_outline')
                            @slot('text', $hints[0])
                        @endcomponent
                    </div>
                </div>

                @push('local.styles')
                    .user-status.online {
                        -webkit-box-shadow: inset -1rem 0 1rem -.4rem #64dd17;
                                box-shadow: inset -1rem 0 1rem -.4rem #64dd17;
                    }
                    .user-status.offline {
                        -webkit-box-shadow: inset -1rem 0 1rem -.4rem #f44336;
                                box-shadow: inset -1rem 0 1rem -.4rem #f44336;
                    }
                @endpush

                @push('local.scripts')
                    var usersTimer;

                    function __organisation(__, obj)
                    {
                        var item_model = __.children('.model');

                        if (obj.status == 'ok')
                        {
                            __.removeClass('hide')

                            $('[data-name=organisation-name]').html(obj.organisation.name)
                            $('[data-name=organisation-capacity]').html(obj.users.length + ' / ' + obj.organisation.user_capacity)
                            $('[data-name=organisation-status]').html(obj.organisation.status ? obj.organisation.days + ' gün kaldı' : 'Pasif').addClass(obj.organisation.status ? '' : 'red-text')

                            if (obj.users.length)
                            {
                                $.each(obj.users, function(key, o) {
                                    var selector = $('[data-id=' + o.id + '].collection-item'),

                                        item = selector.length ? selector : item_model.clone();

                                        item.removeClass('model hide online offline')
                                            .addClass('_tmp d-flex')
                                            .addClass(o.online ? 'online' : 'offline')
                                            .attr('data-id', o.id)

                                        item.find('[data-name=avatar]').attr('src', o.avatar)
                                        item.find('[data-name=name]').html(o.name)
                                        item.find('[data-name=e-mail]').html(o.email)
                                        item.find('[data-name=title]').html(obj.organisation.author == o.id ? 'Organizasyon Yöneticisi' : 'Kullanıcı')

                                        item.appendTo(__)
                                })
                            }
                        }

                        window.clearTimeout(usersTimer)

                        usersTimer = window.setTimeout(function() {
                            vzAjax(__)
                        }, 60000)
                    }
                @endpush
            @else
                <div class="card mb-1" data-card="organisation">
                    <div class="card-content">
                        <span class="card-title">Organizasyon Oluşturun!</span>
                        <p class="grey-text">Bireysel veya ekip olarak çalışmak için size en uygun organizasyonu oluşturun.</p>
                    </div>
                    <div class="card-content right-align">
                        <a href="{{ route('organisation.create.offer') }}" class="btn-flat waves-effect" id="start">Başla</a>
                    </div>
                </div>

                @if (!auth()->user()->intro('welcome.create.organisation') && auth()->user()->verified && auth()->user()->term_version == config('system.term_version'))
                    <div class="tap-target cyan darken-4 white-text" data-target="start">
                        <div class="tap-target-content">
                            <h5>Organizasyon Oluşturun!</h5>
                            <p>Olive'in tüm özelliklerini keşfetmek için hemen denemeye başlayın!</p>
                        </div>
                    </div>

                    @push('local.scripts')
                        _scrollTo({
                            'target': '[data-card=organisation]',
                            'tolerance': '-128px'
                        })

                        $('[data-target=start]').tapTarget({
                            'onClose': function() {
                                vzAjax($('<div />', {
                                    'class': 'json',
                                    'data-method': 'post',
                                    'data-href': '{{ route('intro', 'welcome.create.organisation') }}'
                                }))
                            }
                        })
                        $('[data-target=start]').tapTarget('open')
                    @endpush
                @endif
            @endif
        </div>
        <div class="col s12 xl7">
            @push('local.scripts')
                function __activities(__, obj)
                {
                    var item_model = __.children('li.model');

                    if (obj.status == 'ok')
                    {
                        if (obj.hits.length)
                        {
                            $.each(obj.hits, function(key, o) {
                                var item = item_model.clone();
                                    item.removeClass('model hide').addClass('_tmp').attr('data-id', 'list-item-' + o.id)

                                    item.find('.collapsible-header > span > p').html(o.title)
                                    item.find('.collapsible-header > span > time').attr('data-time', o.updated_at).html(o.updated_at)
                                    item.find('.collapsible-header > [data-name=icon]').html(o.icon)
                                    item.find('.collapsible-body > span').html(o.markdown)

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
                    }
                }
            @endpush

            <ul class="collapsible load json-clear" 
                id="activities"
                data-href="{{ route('dashboard.activities') }}"
                data-skip="0"
                data-take="10"
                data-more-button="#activities-more_button"
                data-callback="__activities"
                data-method="post"
                data-loader="#home-loader"
                data-nothing>
                <li class="nothing hide">
                    @component('components.nothing')
                        @slot('cloud_class', 'white-text')
                        @slot('text', 'Henüz bir aktiviteniz olmadı.')
                    @endcomponent
                </li>
                <li class="model hide">
                    <div class="collapsible-header">
                        <i class="material-icons" data-name="icon"></i>
                        <span>
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

            @component('components.loader')
                @slot('color', 'blue-grey')
                @slot('id', 'home-loader')
            @endcomponent

            <div class="center-align">
                <a
                    class="more hide json"
                    id="activities-more_button"
                    href="#"
                    data-json-target="ul#activities">Daha Fazla</a>
            </div>
        </div>
    </div>

    <ul class="card">
        <li class="card-content">
            <span class="card-title">Güncel Forum Konuları</span>
            <small class="grey-text">İlgili kategorilerde özgürce yardımlaşabilirsiniz.</small>
        </li> 
        @forelse ($threads as $thread)
            @php
                $color_light = '';
                $color_dark = '';

                if ($thread->static)
                {
                    $color_light = 'white lighten-2 grey-text';
                    $color_dark = 'grey lighten-2';
                }
            @endphp
            <li class="card-content {{ $color_dark }}">
                <div class="d-flex">
                    <span class="align-self-center center-align" style="margin: 0 1rem 0 0;">
                        <a class="d-block" data-tooltip="{{ $thread->user->name }}" data-position="right" href="{{ route('user.profile', $thread->user_id) }}">
                            <img alt="Avatar" src="{{ $thread->user->avatar() }}" class="circle" style="width: 64px; height: 64px;" />
                        </a>
                    </span>
                    <div class="align-self-center">
                        <div class="d-flex">
                            @if ($thread->closed)
                                <i class="material-icons grey-text text-darken-2 tiny" data-tooltip="Kapalı">lock</i>
                            @endif

                            @if ($thread->static)
                                <i class="material-icons grey-text text-darken-2 tiny" data-tooltip="Sabit">terrain</i>
                            @endif

                            @if ($thread->question == 'solved')
                                <i class="material-icons grey-text text-darken-2 tiny" data-tooltip="Çözüldü">check</i>
                            @elseif ($thread->question == 'unsolved')
                                <i class="material-icons grey-text text-darken-2 tiny" data-tooltip="Soru">help</i>
                            @endif
                        </div>
                        <a href="{{ $thread->route() }}" class="d-flex">
                            <span class="card-title card-title-small align-self-center mb-0">{{ $thread->subject }}</span>
                        </a>
                        <p class="grey-text">
                            @if (count($thread->replies))
                                <time class="timeago grey-text text-darken-2" data-time="{{ $thread->updated_at }}">{{ date('d.m.Y H:i', strtotime($thread->updated_at)) }}</time>
                                <span>yanıtladı</span>
                                <a href="{{ route('user.profile', $thread->replies->last()->user->id) }}">{{ '@'.$thread->replies->last()->user->name }}</a>
                            @else
                                <time class="timeago grey-text text-darken-2" data-time="{{ $thread->created_at }}">{{ date('d.m.Y H:i', strtotime($thread->created_at)) }}</time>
                                <span>yazdı</span>
                                <a href="{{ route('user.profile', $thread->user_id) }}">{{ '@'.$thread->user->name }}</a>
                            @endif
                        </p>
                        @if (count($thread->replies))
                            {!! $thread->replies()->paginate(10)->onEachSide(1)->setPath($thread->route())->links('vendor.pagination.materializecss_in') !!}
                        @endif
                    </div>
                    <div class="align-self-center ml-auto hide-on-med-and-down">
                        <a href="{{ route('forum.category', $thread->category->slug) }}" class="chip waves-effect center-align {{ $color_light }}">{{ $thread->category->name }}</a>
                        <span class="badge d-flex grey-text justify-content-end">
                            <span class="align-self-center">{{ count($thread->replies) }}</span>
                            <i class="material-icons align-self-center" style="margin: 0 0 0 .4rem;">reply</i>
                        </span>
                        <span class="badge d-flex grey-text justify-content-end">
                            <span class="align-self-center">{{ $thread->hit }}</span>
                            <i class="material-icons align-self-center" style="margin: 0 0 0 .4rem;">remove_red_eye</i>
                        </span>
                    </div>
                </div>
            </li>
        @empty
            <li class="card-content grey-text">
                Üzgünüm, daha fazla içerik yok.
            </li>
        @endforelse
    </ul>
@endsection

@push('local.scripts')
    @if (session('validate'))
        M.toast({ html: 'Tebrikler! E-posta adresiniz doğrulandı!', classes: 'green darken-2' })
    @endif

    @if (session('leaved'))
        M.toast({ html: 'Organizasyondan başarılı bir şekilde ayrıldınız.', classes: 'green darken-2' })
    @endif

    @if (session('deleted'))
        M.toast({ html: 'Organizasyon Silindi', classes: 'green darken-2' })
    @endif
@endpush

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/owl.carousel.min.js?v='.config('system.version')) }}"></script>
@endpush
