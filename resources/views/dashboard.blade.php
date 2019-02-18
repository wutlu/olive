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
        border-radius: 1rem 1rem 0 0;
    }
    .wildcard {
        background-color: #222;
    }
@endpush

@section('wildcard')
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
                                'class': 'modal-close waves-effect btn-flat cyan-text',
                                'html': buttons.ok
                            })
                        ]
                    })
                @endpush
            @endif
        @endforeach
    @endif

    @if (count($carousels))
        <div class="carousel carousel-slider center">
            @php
            $i = 0;
            @endphp
                @foreach ($carousels as $carousel)
                <div class="{{ implode(' ', [ 'carousel-item', $i == 0 ? 'active' : '', 'white-text' ]) }}">
                    <h2>{{ $carousel->title }}</h2>
                    <div class="markdown">
                        {!! $carousel->markdown() !!}
                    </div>
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
    <div class="row">
        <div class="col s12">
            <div class="fast-menu">
                <a href="{{ route('forum.index') }}">
                    <i class="material-icons">forum</i>
                    <span class="d-block">Forum</span>
                </a>
                <a href="{{ route('data_pool.dashboard') }}">
                    <i class="material-icons">hearing</i>
                    <span class="d-block">Veri Havuzu</span>
                </a>
                <a href="{{ route('realtime.stream') }}">
                    <i class="material-icons">watch_later</i>
                    <span class="d-block">Gerçek Zamanlı</span>
                </a>
                <a href="{{ route('search.dashboard') }}">
                    <i class="material-icons">youtube_searched_for</i>
                    <span class="d-block">Arama Motoru</span>
                </a>
                <a href="{{ route('trend.live') }}">
                    <i class="material-icons">trending_up</i>
                    <span class="d-block">Trend Analizi <sup>Beta</sup></span>
                </a>
                <a data-modal-alert="Bu bölümü yapıyoruz. Takipte kalın!" style="opacity: .4;" href="#">
                    <i class="material-icons">pie_chart</i>
                    <span class="d-block">Model Analizi</span>
                </a>
                <a data-modal-alert="Bu bölümü yapıyoruz. Takipte kalın!" style="opacity: .4;" href="#">
                    <i class="material-icons">access_alarm</i>
                    <span class="d-block">Alarmlar</span>
                </a>
                <a data-modal-alert="Bu bölümü yapıyoruz. Takipte kalın!" style="opacity: .4;" href="#">
                    <i class="material-icons">settings</i>
                    <span class="d-block">Araçlar</span>
                </a>
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
                            <p class="grey-text">{{ count($user->organisation->users) }}/{{ $user->organisation->capacity }} kullanıcı</p>
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
