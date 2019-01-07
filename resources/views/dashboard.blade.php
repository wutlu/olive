@extends('layouts.app', [
    'sidenav_fixed_layout' => true
])

@push('local.scripts')
    $('.carousel.carousel-slider').carousel({
        fullWidth: true,
        indicators: true
    })
@endpush

@section('wildcard')
    @if (count($modals))
        @foreach ($modals as $carousel)
            @if ($carousel->modal)
                @push('local.scripts')
                    modal({
                        'id': 'carousel-{{ $carousel->id }}',
                        'body': '{!! str_replace(PHP_EOL, '', $carousel->markdown()) !!}',
                        'size': 'modal-large',
                        'title': '{{ $carousel->title }}',
                        'options': {},
                        'footer': [
                            $('<a />', {
                                'href': '#',
                                'class': 'modal-close waves-effect btn-flat cyan-text',
                                'html': buttons.ok
                            })
                        ]
                    });
                @endpush
            @endif
        @endforeach
    @endif
    @if (count($carousels))
        <div class="carousel carousel-slider center cyan darken-4 z-depth-1">
            @php
            $i = 0;
            @endphp
                @foreach ($carousels as $carousel)
                <div class="{{ implode(' ', [ 'carousel-item', $i == 0 ? 'active' : '', 'white-text' ]) }}">
                    <h2>{{ $carousel->title }}</h2>
                    {!! $carousel->markdown() !!}
                    <div class="{{ implode(' ', [ 'anim', $carousel->pattern ]) }}"></div>

                    @if ($carousel->button_text)
                        <a href="{{ $carousel->button_action }}" class="btn-flat waves-effect waves-red white-text">
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
@endsection

@section('content')
    <div class="fast-menu">
        <a href="{{ route('twitter.keyword.list') }}" class="card-panel hoverable waves-effect" data-tooltip="Twitter Veri Havuzu" data-position="right">
            <img alt="Twitter Veri Havuzu" src="{{ asset('img/icons/filter.png') }}" />
        </a>
        <a style="opacity: .4;" href="#" class="card-panel hoverable waves-effect" data-tooltip="Monitörler" data-position="right">
            <img alt="Monitorler" src="{{ asset('img/icons/analytics.png') }}" />
        </a>
        <a href="{{ route('realtime.stream') }}" class="card-panel hoverable waves-effect" data-tooltip="Gerçek Zamanlı" data-position="right">
            <img alt="Gerçek Zamanlı" src="{{ asset('img/icons/realtime.png') }}" />
        </a>
        <a href="{{ route('trend.live') }}" class="card-panel hoverable waves-effect" data-tooltip="Trend Analizi" data-position="right">
            <img alt="Trend Analizi" src="{{ asset('img/icons/trends.png') }}" />
        </a>
        <a style="opacity: .4;" href="#" class="card-panel hoverable waves-effect" data-tooltip="Geçmiş Veri" data-position="right">
            <img alt="Geçmiş Veri" src="{{ asset('img/icons/archive.png') }}" />
        </a>
        <a style="opacity: .4;" href="#" class="card-panel hoverable waves-effect" data-tooltip="Alarmlar" data-position="right">
            <img alt="Alarmlar" src="{{ asset('img/icons/alarm.png') }}" />
        </a>
        <a style="opacity: .4;" href="#" class="card-panel hoverable waves-effect" data-tooltip="Araçlar" data-position="right">
            <img alt="Araçlar" src="{{ asset('img/icons/tools.png') }}" />
        </a>
    </div>
    <div class="row">
        <div class="col xl4 l5 s12">
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
                    <a class="card-content card-content-image d-block waves-effect" href="{{ route('settings.organisation') }}">
                        <span class="card-title">{{ $user->organisation->name }}</span>
                        <p class="grey-text">{{ count($user->organisation->users) }}/{{ $user->organisation->capacity }} kullanıcı</p>
                        @if ($user->id == $user->organisation->user_id)
                            @if ($user->organisation->status)
                            <p class="grey-text">{{ $user->organisation->days() }} gün kaldı</p>
                            @else
                            <p class="red-text">Pasif</p>
                            @endif
                        @endif
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
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Başlayın</span>
                        <p class="grey-text">Profesyonel bir ortamda tüm modüllerden faydalanabilmek için hemen bir organizasyon oluşturun.</p>
                    </div>
                    <div class="card-action">
                        <a href="{{ route('organisation.create.select') }}" id="start">Plan Seçin</a>
                    </div>
                </div>

                @if (!auth()->user()->intro('welcome.create.organisation') && auth()->user()->verified)
                    <div class="tap-target cyan darken-4 white-text" data-target="start">
                        <div class="tap-target-content">
                            <h5>Organizasyon Oluşturun</h5>
                            <p>Hemen profesyonel olarak başlayın!</p>
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
                        });
                        $('[data-target=start]').tapTarget('open');
                    @endpush
                @endif
            @endif
        </div>
        <div class="col xl8 l7 s12">
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

                        $('#home-loader').hide()
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
                <button class="btn-flat waves-effect hide json"
                        id="activities-more_button"
                        type="button"
                        data-json-target="ul#activities">Daha Fazla</button>
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
        M.toast({ html: 'Organizasyon silindi.', classes: 'green darken-2' })
    @endif

@endpush
