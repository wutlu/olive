@extends('layouts.app', [
    'sidenav_fixed_layout' => true
])

@section('content')
    <div class="row">
        <div class="col s12">
            <div class="fast-menu">
                <a href="{{ route('twitter.keyword.list') }}" class="card-panel hoverable waves-effect" data-tooltip="Twitter Veri Havuzu" data-position="right">
                    <img alt="Twitter Veri Havuzu" src="{{ asset('img/icons/keywords.png') }}" />
                </a>
                <a style="opacity: .4;" href="#" class="card-panel hoverable waves-effect" data-tooltip="Monitörler" data-position="right">
                    <img alt="Monitorler" src="{{ asset('img/icons/analytics.png') }}" />
                </a>
                <a href="{{ route('realtime') }}" class="card-panel hoverable waves-effect" data-tooltip="Gerçek Zamanlı" data-position="right">
                    <img alt="Gerçek Zamanlı" src="{{ asset('img/icons/realtime.png') }}" />
                </a>
                <a style="opacity: .4;" href="#" class="card-panel hoverable waves-effect" data-tooltip="Trendler" data-position="right">
                    <img alt="Trendler" src="{{ asset('img/icons/trends.png') }}" />
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
        </div>
        <div class="col xl4 l5 s12">
            @if (@auth()->user()->organisation_id)
                <div class="card" id="organisation-card">
                    <div class="card-image">
                        <img src="{{ asset('img/user-background.jpg') }}" alt="" />
                        <span class="card-title">{{ $user->organisation->name }}</span>
                        <a href="{{ route('settings.organisation') }}" class="btn-floating btn-large halfway-fab waves-effect white">
                            <i class="material-icons black-text">settings</i>
                        </a>
                    </div>
                    <div class="card-content">
                        <p class="grey-text">{{ count($user->organisation->users) }}/{{ $user->organisation->capacity }} kullanıcı</p>
                        @if ($user->id == $user->organisation->user_id)
                            @if ($user->organisation->status)
                            <p class="grey-text">{{ $user->organisation->days() }} gün kaldı.</p>
                            @else
                            <p class="red-text">Pasif</p>
                            @endif
                        @endif
                    </div>
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
                    <div class="card-image">
                        <img src="{{ asset('img/card-header.jpg') }}" alt="" />
                        <span class="card-title">Başlayın</span>
                    </div>
                    <div class="card-content">
                        <p class="grey-text">Profesyonel bir ortamda tüm modüllerden faydalanabilmek için hemen bir organizasyon oluşturun.</p>
                    </div>
                    <div class="card-action">
                        <a href="{{ route('organisation.create.select') }}" id="start">Plan Seçin</a>
                    </div>
                </div>

                @if (!auth()->user()->intro('welcome.create.organisation') && auth()->user()->verified)
                    <div class="tap-target teal white-text" data-target="start">
                        <div class="tap-target-content">
                            <h5>Organizasyon Oluşturun</h5>
                            <p>Hemen profesyonel olarak başlayın!</p>
                        </div>
                    </div>

                    @push('local.scripts')
                    $('.tap-target').tapTarget({
                        'onClose': function() {
                            vzAjax($('<div />', {
                                'class': 'json',
                                'data-method': 'post',
                                'data-href': '{{ route('intro', 'welcome.create.organisation') }}'
                            }))
                        }
                    });
                    $('.tap-target').tapTarget('open');
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
                        item_model.addClass('d-none')

                        if (obj.hits.length)
                        {
                            $.each(obj.hits, function(key, o) {
                                var item = item_model.clone();
                                    item.removeClass('model d-none').addClass('_tmp').attr('data-id', 'list-item-' + o.id)

                                    item.find('.collapsible-header > span > p').html(o.title)
                                    item.find('.collapsible-header > span > time').attr('data-time', o.updated_at).html(o.updated_at)
                                    item.find('.collapsible-header > i.icon').html(o.icon)
                                    item.find('.collapsible-body > span').html(o.markdown)

                                    if (o.markdown_color)
                                    {
                                        item.find('.collapsible-body').css({ 'background-color': o.markdown_color })
                                    }

                                    if (o.button_type)
                                    {
                                        if (o.button_type == 'ajax')
                                        {
                                            var button = $('<a />', {
                                                'class': 'json ' + o.button_class,
                                                'html': o.button_text,
                                                'data-href': o.button_action,
                                                'data-method': o.button_method
                                            });
                                        }
                                        else
                                        {
                                            var button = $('<a />', {
                                                'class': o.button_class,
                                                'html': o.button_text,
                                                'href': o.button_action,
                                                'data-method': o.button_method
                                            });
                                        }

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
                data-nothing>
                <li class="nothing d-none">
                    <div class="not-found">
                        <i class="material-icons">cloud</i>
                        <i class="material-icons">cloud</i>
                        <i class="material-icons">wb_sunny</i>
                    </div>
                </li>
                <li class="model d-none">
                    <div class="collapsible-header">
                        <i class="material-icons icon"></i>
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
                @slot('color', 'purple')
                @slot('id', 'home-loader')
            @endcomponent

            <div class="center-align">
                <button class="btn-flat waves-effect d-none json"
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
