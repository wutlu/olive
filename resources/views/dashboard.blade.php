@extends('layouts.app', [
    'sidenav_fixed_layout' => true
])

@push('local.scripts')
    $('.carousel.carousel-slider').carousel({
        fullWidth: true,
        indicators: true
    })
@endpush

@push('local.styles')
    .carousel-slider {
        margin-bottom: 1rem;
    }
@endpush

@section('content')
    @if (count($carousels))
        <div class="carousel carousel-slider center">
            @php
            $i = 0;
            @endphp
                @foreach ($carousels as $carousel)
                <div class="{{ implode(' ', [ 'carousel-item', $i == 0 ? 'active' : '', '' ]) }}">
                    <h2 class="teal-text text-darken-2">{{ $carousel->title }}</h2>
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
                                'html': buttons.ok
                            })
                        ]
                    })
                @endpush
            @endif
        @endforeach
    @endif

    <div class="row">
        <div class="col s12">
            <div class="fast-menu">
                @foreach ([
                    [
                        'route' => route('forum.index'),
                        'icon' => 'forum',
                        'name' => 'Forum'
                    ],
                    [
                        'route' => route('data_pool.dashboard'),
                        'icon' => 'hearing',
                        'name' => 'Veri Havuzu'
                    ],
                    [
                        'route' => route('realtime.stream'),
                        'icon' => 'watch_later',
                        'name' => 'Gerçek Zamanlı'
                    ],
                    [
                        'route' => route('search.dashboard'),
                        'icon' => 'youtube_searched_for',
                        'name' => 'Arama Motoru'
                    ],
                    [
                        'route' => route('trend.live'),
                        'icon' => 'trending_up',
                        'name' => 'Trend Analizi'
                    ],
                    [
                        'route' => route('alarm.dashboard'),
                        'icon' => 'access_alarm',
                        'name' => 'Alarmlar'
                    ]
                ] as $key => $item)
                    <a href="{{ $item['route'] }}">
                        <i class="material-icons">{{ $item['icon'] }}</i>
                        <span class="d-block">{{ $item['name'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="col s12 xl5">
            @if (@auth()->user()->organisation_id)
                @if (!auth()->user()->intro('search.module'))
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
                <div class="card">
                    <a class="card-content card-content-image d-flex justify-content-between waves-effect" href="{{ route('settings.organisation') }}">
                        <span>
                            <span class="card-title">{{ $user->organisation->name }}</span>
                            <p class="grey-text">{{ count($user->organisation->users) }} / {{ $user->organisation->user_capacity }} kullanıcı</p>
                            @if ($user->id == $user->organisation->user_id)
                                @if ($user->organisation->status)
                                    <p class="grey-text">{{ $user->organisation->days() }} gün kaldı</p>
                                @else
                                    <p class="red-text">Pasif</p>
                                @endif
                            @endif
                        </span>
                        <i class="material-icons">settings</i>
                    </a>
                    <ul class="collection">
                        @foreach ($user->organisation->users as $u)
                        <li class="collection-item avatar">
                            <img src="{{ $u->avatar() }}" alt="" class="circle">
                            <span class="title">{{ $u->name }}</span>
                            <p class="grey-text">{{ $u->email }}</p>
                            <p class="grey-text">{{ $u->id == $user->organisation->user_id ? 'Organizasyon Sahibi' : 'Kullanıcı' }}</p>
                        </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="card mb-1">
                    <div class="card-image">
                        <img src="{{ asset('img/md-s/21.jpg') }}" alt="Image" />
                        <span class="card-title white-text d-flex">
                            Teklif Alın
                        </span>
                    </div>
                    <div class="card-content">
                        <p class="grey-text">Size uygun en iyi teklifler için hemen bizimle iletişime geçin..</p>
                    </div>
                    <div class="card-action">
                        <a href="{{ route('organisation.create.offer') }}" id="start">Başlayın</a>
                    </div>
                </div>

                @if (!auth()->user()->intro('welcome.create.organisation') && auth()->user()->verified && auth()->user()->term_version == config('system.term_version'))
                    <div class="tap-target cyan darken-4 white-text" data-target="start">
                        <div class="tap-target-content">
                            <h5>Teklif Alın</h5>
                            <p>Olive ayrıcalıklarından faydalanarak profesyonel dünyada yerinizi almak için hemen başlayın.</p>
                        </div>
                    </div>

                    @push('local.scripts')
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
                    var ul = $('#activities');
                    var item_model = ul.children('li.model');

                    if (obj.status == 'ok')
                    {
                        item_model.addClass('hide')

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

                                    item.appendTo(ul)
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
                    @endcomponent
                </li>
                <li class="model hide">
                    <div class="collapsible-header">
                        <i class="material-icons" data-name="icon"></i>
                        <span>
                            <p></p>
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
                @slot('color', 'cyan')
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
