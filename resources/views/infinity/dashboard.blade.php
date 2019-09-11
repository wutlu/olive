@extends('layouts.app', [
    'sidenav_layout' => true,
    'footer_hide' => true,
    'logo' => asset('img/8vz-white.svg'),
    'wide' => true,
    'breadcrumb' => [
        [
            'text' => 'Infinity Veri Zone (8vz)'
        ]
    ],
    'description' => 'Infinity Veri Zone (8vz), Veri Zone Teknoloji ürünüdür. Güncel sosyal medya ve haber trendlerine canlı bir şekilde ulaşmanızı sağlar.'
])

@push('local.scripts')
    var incoming_trends = 0;
    var outbound_trends = 0;

    $(document).on('click', '[data-trigger=run]', function() {
        var __ = $(this);

        if (__.data('status') == 'on')
        {
            __.data('status', 'off')
            __.find('i.material-icons').html('play_arrow')
            __.removeClass('pulse red')
            __.addClass('blue-grey')

            $('[data-module=' + __.data('name') + ']').css({ 'opacity': .2 })

            M.toast({ html: 'Canlı Trend Durduruldu', 'classes': 'red' })

            setTimeout(function() {
                window.clearTimeout(window[__.data('name') + '_timer'])
            }, 1000)
        }
        else
        {
            __.data('status', 'on')
            __.find('i.material-icons').html('pause')
            __.addClass('pulse red')
            __.removeClass('blue-grey')

            $('[data-module=' + __.data('name') + ']').css({ 'opacity': 1 })

            M.toast({ html: 'Canlı Trend Başlatıldı', 'classes': 'green' })

            vzAjax($('[data-module=' + __.data('name') + ']'))
        }
    })

    function __trends(__, obj)
    {
        if (obj.status == 'ok')
        {
            var model = __.children('.model');

            if (obj.data)
            {
                __.find('.item:not(.model)').addClass('old')

                $.each(obj.data, function(key, o) {
                    var links = {
                        'olive': false,
                        'google': false,
                        'twitter': false,
                        'youtube': false,
                        'sozluk': false
                    };

                    var status = __.find('[data-id=' + o.data.id + ']').length ? 'exists' : 'new';
                    var item = (status == 'exists') ? __.find('[data-id=' + o.data.id + ']') : model.clone();
                        item.removeClass('model hide old').attr('data-id', o.data.id)

                        item.find('[data-name=hit]').html(o.hit)

                        var rank = item.find('[data-name=rank]');
                            rank.html(o.rank)
                            rank.removeClass('red-text green-text grey-text blue-text')

                        if (o.ranks)
                        {
                            var first_rank = o.ranks[o.ranks.length - 2];
                            var last_rank = o.ranks[o.ranks.length - 1];

                            if (last_rank > first_rank)
                            {
                                rank.addClass('red-text')
                            }
                            else if (last_rank < first_rank)
                            {
                                rank.addClass('green-text')
                            }

                            item.find('.collapsible-header').removeClass('red lighten-4')
                        }
                        else
                        {
                            rank.addClass('blue-text')

                            if (o.hit >= 20)
                            {
                                item.find('.collapsible-header').addClass('red lighten-4')
                            }
                        }

                        if (o.data.image)
                        {
                            item.find('[data-name=image]').attr('src', o.data.image).removeClass('hide')
                        }

                        if (__.data('module') == 'twitter_tweet')
                        {
                            var avatar = item.find('[data-name=image]');
                                avatar.attr('src', o.data.user.image)
                                      .removeClass('hide')
                                      .addClass('circle')
                                      .attr('alt', o.data.user.name)

                            if (o.data.user.verified)
                            {
                                avatar.addClass('verified')
                            }

                            item.find('[data-name=title-1]').removeClass('hide').html(o.data.user.name)
                            item.find('[data-name=title-2]').removeClass('hide').html('@' + o.data.user.screen_name)
                            item.find('[data-name=created_at]').removeClass('hide').html(o.data.created_at)

                            var date = o.data.created_at.split('-');

                            links.olive = '/db/{{ config('system.db.alias') }}__twitter-tweets-' + date[0] + '.' + date[1] + '/tweet/' + o.data.id;
                            links.twitter = 'https://twitter.com/' + o.data.user.screen_name + '/status/' + o.data.id;
                        }
                        else if (__.data('module') == 'twitter_hashtag')
                        {
                            item.find('[data-name=title]').html(o.data.key)

                            links.olive = '{{ route('search.dashboard') }}?q="' + o.data.key + '"';
                            links.twitter = 'https://twitter.com/search?q=' + encodeURI(o.data.key);
                            links.google = 'https://www.google.com/search?q=' + encodeURI(o.data.key);
                        }
                        else if (__.data('module') == 'instagram_hashtag')
                        {
                            item.find('[data-name=title]').html(o.data.key)

                            links.olive = '{{ route('search.dashboard') }}?q="' + o.data.key + '"';
                            links.instagram = 'https://www.instagram.com/explore/tags/' + encodeURI(o.data.key) + '/';
                            links.google = 'https://www.google.com/search?q=' + encodeURI(o.data.key);
                        }
                        else if (__.data('module') == 'news')
                        {
                            item.find('[data-name=title]').html(o.data.title)

                            links.olive = '{{ route('search.dashboard') }}?q="' + o.data.title + '"';
                            links.google = 'https://www.google.com/search?q=' + encodeURI(o.data.title);
                        }
                        else if (__.data('module') == 'blog')
                        {
                            item.find('[data-name=title]').html(o.data.title)

                            links.olive = '{{ route('search.dashboard') }}?q="' + o.data.title + '"';
                            links.google = 'https://www.google.com/search?q=' + encodeURI(o.data.title);
                        }
                        else if (__.data('module') == 'entry')
                        {
                            item.find('[data-name=title]').html(o.data.title)

                            links.olive = '{{ route('search.dashboard') }}?q="' + o.data.title + '"';
                            links.google = 'https://www.google.com/search?q=' + encodeURI(o.data.title);
                            links.sozluk = o.data.url;
                        }
                        else if (__.data('module') == 'google')
                        {
                            item.find('[data-name=title]').html(o.data.title)

                            links.olive = '{{ route('search.dashboard') }}?q="' + o.data.title + '"';
                            links.google = 'https://www.google.com/search?q=' + encodeURI(o.data.title);
                            links.sozluk = o.data.url;
                        }
                        else if (__.data('module') == 'youtube_video')
                        {
                            item.find('[data-name=title]').html(o.data.title)

                            links.olive = '{{ route('search.dashboard') }}?q="' + o.data.title + '"';
                            links.google = 'https://www.google.com/search?q=' + encodeURI(o.data.title);
                            links.youtube = 'https://www.youtube.com/watch?v=' + o.data.id;

                            item.find('[data-name=image]').attr('src', 'https://i.ytimg.com/vi/' + o.data.id + '/hqdefault.jpg').removeClass('hide')
                        }

                        $.each(links, function(ku, u) {
                            if (u)
                            {
                                item.find('[data-name=link-' + ku + ']').attr('href', u).removeClass('hide')

                                if (ku == 'sozluk')
                                {
                                    var site = 'Sözlük';

                                    if (u.indexOf('eksisozluk.com') != -1)
                                    {
                                        site = 'Ekşi Sözlük';
                                    }
                                    else if (u.indexOf('incisozluk.com.tr') != -1)
                                    {
                                        site = 'İnci Sözlük';
                                    }
                                    else if (u.indexOf('instela.com') != -1)
                                    {
                                        site = 'Instela';
                                    }
                                    else if (u.indexOf('uludagsozluk.com') != -1)
                                    {
                                        site = 'Uludağ Sözlük';
                                    }

                                    item.find('[data-name=link-' + ku + ']').find('[data-name=alias]').html(site)
                                }
                            }
                        })

                        if (o.data.text)
                        {
                            item.find('[data-name=text]').removeClass('hide').html(o.data.text)
                        }

                        item.appendTo(__)

                    if (status == 'new')
                    {
                        incoming_trends++;
                    }
                })

                $('<li />', {
                    'class': 'item center-align p-1',
                    'html': $('<a />', {
                        'href': 'https://veri.zone/',
                        'html': '+' + obj.more + ' trend Olive\'de'
                    })
                }).appendTo(__)

                outbound_trends = outbound_trends + __.find('.item.old').length;

                __.find('.item.old').remove()
            }
            else
            {
                M.toast({ html: 'Gösterilecek trend bulunamadı. Olduğunda ekran güncellenecektir.', 'classes': 'teal darken-2' })
            }

            $('[data-name=incoming-trends]').html(incoming_trends)
            $('[data-name=outbound-trends]').html(outbound_trends)

            window.clearTimeout(window[__.data('module') + '_timer'])
            window[__.data('module') + '_timer'] = window.setTimeout(function() {
                vzAjax(__)
            }, 30000)
        }
    }
@endpush

@push('local.styles')
    [data-id=trend_list] {
        min-height: 200px;
        max-height: calc(100vh);
        overflow: auto;
        background-image: url(../img/8vz-opacity.svg);
        background-repeat: no-repeat;
        background-position: center;
        background-size: 50%;
    }

    .image.verified {
        -webkit-box-shadow: 0 0 0 4px #bbdefb;
                box-shadow: 0 0 0 4px #bbdefb;
    }
@endpush

@section('content')
    <div class="card-deck sortable">
        @foreach ($trends as $trend)
            <script>
            {{ 'var '.$trend['module'].'_timer' }};
            </script>
            <div class="card card-unstyled">
                <div class="card-content">
                    <span class="card-title d-flex">
                        <a href="#" class="btn-floating btn-flat align-self-center d-flex handle mr-1 drag-btn">
                            <i class="material-icons align-self-center grey-text text-darken-2">drag_handle</i>
                        </a>
                        <span class="align-self-center" id="card-{{ $trend['module'] }}">{{ $trend['title'] }}</span>
                        <div class="d-flex ml-auto">
                            <a
                                href="#"
                                class="btn-floating blue-grey darken-2 d-flex play-btn"
                                data-trigger="run"
                                data-status="off"
                                data-name="{{ $trend['module'] }}">
                                <i class="material-icons align-self-center">play_arrow</i>
                            </a>
                        </div>
                    </span>
                </div>
                <ul
                    id="trend_list-{{ $trend['module'] }}"
                    data-id="trend_list"
                    class="collapsible"
                    data-href="{{ route('infinity.live') }}"
                    data-module="{{ $trend['module'] }}"
                    data-method="post"
                    data-callback="__trends">
                    <li class="item model hide">
                        <div class="collapsible-header" style="padding: .4rem;">
                            <span class="rank" data-name="rank"></span>
                            <span class="rank rank-wide" data-name="hit"></span>
                            <img alt="..." class="hide image" data-name="image" />
                            <span data-name="title">
                                <p class="grey-text text-darken-2 mb-0 hide" data-name="title-1"></p>
                                <p class="grey-text mb-0 hide" data-name="title-2"></p>
                            </span>
                            <i class="material-icons arrow">keyboard_arrow_down</i>
                        </div>
                        <div class="collapsible-body">
                            <div class="card card-unstyled mb-1">
                                <div class="card-content hide" data-name="text"></div>
                                <div class="collection collection-unstyled">
                                    <div class="collection-item grey-text hide" data-name="created_at"></div>
                                    <a href="#" target="_blank" class="collection-item" data-name="link-olive">
                                        <span class="d-flex">
                                            <i class="material-icons align-self-center mr-1">link</i>
                                            <span class="align-self-center">Olive'de göster</span>
                                        </span>
                                    </a>
                                    <a href="#" target="_blank" class="collection-item hide" data-name="link-google">
                                        <span class="d-flex">
                                            <i class="material-icons align-self-center mr-1">link</i>
                                            <span class="align-self-center">Google'da göster</span>
                                        </span>
                                    </a>
                                    <a href="#" target="_blank" class="collection-item hide" data-name="link-twitter">
                                        <span class="d-flex">
                                            <i class="material-icons align-self-center mr-1">link</i>
                                            <span class="align-self-center">Twitter'da göster</span>
                                        </span>
                                    </a>
                                    <a href="#" target="_blank" class="collection-item hide" data-name="link-youtube">
                                        <span class="d-flex">
                                            <i class="material-icons align-self-center mr-1">link</i>
                                            <span class="align-self-center">YouTube'da göster</span>
                                        </span>
                                    </a>
                                    <a href="#" target="_blank" class="collection-item hide" data-name="link-sozluk">
                                        <span class="d-flex">
                                            <i class="material-icons align-self-center mr-1">link</i>
                                            <span class="align-self-center">Sözlük'de göster</span>
                                            <span class="align-self-center ml-auto" data-name="alias"></span>
                                        </span>
                                    </a>
                                    <a href="#" target="_blank" class="collection-item hide" data-name="link-instagram">
                                        <span class="d-flex">
                                            <i class="material-icons align-self-center mr-1">link</i>
                                            <span class="align-self-center">Instagram'da göster</span>
                                            <span class="align-self-center ml-auto" data-name="alias"></span>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        @endforeach
    </div>

    <br />

    <div class="card card-unstyled">
        <div class="card-content">
            <div class="d-flex flex-wrap">
                <div  class="p-1">
                    <div class="red-text">
                        @component('components.alert')
                            @slot('icon', 'info_outline')
                            @slot('text', 'Kırmızı, trendin düşüşte olduğunu gösterir.')
                        @endcomponent
                    </div>
                    <div class="green-text">
                        @component('components.alert')
                            @slot('icon', 'info_outline')
                            @slot('text', 'Yeşil, trendin yükselişte olduğunu gösterir.')
                        @endcomponent
                    </div>
                    <div class="blue-text">
                        @component('components.alert')
                            @slot('icon', 'info_outline')
                            @slot('text', 'Mavi, trendin yeni olduğunu gösterir.')
                        @endcomponent
                    </div>
                    <div class="grey-text">
                        @component('components.alert')
                            @slot('icon', 'info_outline')
                            @slot('text', 'Gri, trendin yerini koruduğunu gösterir.')
                        @endcomponent
                    </div>
                    <div class="red lighten-4">
                        @component('components.alert')
                            @slot('icon', 'info_outline')
                            @slot('text', 'Kırmızı zemin, olağanüstü bir hareketlilik olduğunu gösterir.')
                        @endcomponent
                    </div>
                </div>
                <div  class="p-1">
                    <div class="grey-text">
                        @component('components.alert')
                            @slot('icon', 'multiline_chart')
                            @slot('text', 'Ekrana düşen toplam trend sayısı, <span data-name="incoming-trends">0</span>')
                        @endcomponent
                    </div>
                    <div class="grey-text">
                        @component('components.alert')
                            @slot('icon', 'multiline_chart')
                            @slot('text', 'Ekrandan çıkan toplam trend sayısı, <span data-name="outbound-trends">0</span>')
                        @endcomponent
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="page-footer">
        <div class="container container-wide pt-2 pb-2">
            <div class="row">
                <div class="col l4 s12">
                    <img id="vz-logo" src="{{ asset('img/veri.zone_logo-grey.svg') }}" alt="veri.zone-logo" />
                    <p class="grey-text">© {{ date('Y') }} <a href="https://veri.zone/">Veri Zone Teknoloji</a> | Tüm hakları saklıdır.</p>

                    <a target="_blank" href="https://twitter.com/veri8zone" class="btn-flat btn-small btn-floating social-icon mt-1">
                        <i class="social-icon icon-twitter white-text">&#xe803;</i>
                    </a>
                    <a target="_blank" href="https://www.linkedin.com/company/veri-zone" class="btn-flat btn-small btn-floating social-icon mt-1">
                        <i class="social-icon icon-linkedin white-text">&#xe805;</i>
                    </a>
                    <a target="_blank" href="https://www.instagram.com/veri8zone/" class="btn-flat btn-small btn-floating social-icon mt-1">
                        <i class="social-icon icon-instagram white-text">&#xe808;</i>
                    </a>
                </div>
                <div class="col l4 offset-l2 s12">
                    <a href="{{ route('page.view', 'iletisim') }}" class="d-table black-text">İLETİŞİM</a>
                    <p class="grey-text">Mustafa Kemal Mh. Dumlupınar Blv. ODTÜ Teknokent Bilişim İnovasyon Merkezi</p>
                    <p class="grey-text">280/G No:1260 Alt Zemin Kat Çankaya, Ankara</p>
                    <a class="grey-text text-darken-2 d-table" href="tel:+908503021630">+90 850 302 16 30</a>
                    <a class="grey-text text-darken-2 d-table" href="mailto:bilgi@veri.zone">bilgi@veri.zone</a>
                </div>
                <div class="col l2 s12">
                    <ul class="mt-0 mb-1">
                        <li class="grey-text text-darken-2 mb-1">
                            Olive; "<a href="{{ route('page.view', 'gizlilik-politikasi') }}">Gizlilik</a> ve <a href="{{ route('page.view', 'cerez-politikasi') }}">Çerez</a> Politikası" ve "<a href="{{ route('page.view', 'kullanim-kosullari') }}">Kullanım Koşulları</a>" Infinity Veri Zone (8vz.net) web sitesini de kapsamaktadır.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
@endsection

@push('local.styles')
    .card-deck {
      display: -ms-flexbox;
      display: flex;

      -ms-flex-direction: column;
          flex-direction: column;

      -ms-flex-flow: row wrap;
          flex-flow: row wrap;
    }
    .card-deck > .card {
      -ms-flex: 1 0 0%;
          flex: 1 0 0%;

      -ms-flex-direction: column;
          flex-direction: column;

      max-width: 25%;
      min-width: 25%;
    }

    @media only screen and (max-width: 1366px) {
      .card-deck > .card {
        max-width: 50%;
        min-width: 50%;
      }
    }

    @media only screen and (max-width: 992px) {
      .card-deck > .card {
        max-width: 100%;
        min-width: 100%;
      }
    }
@endpush

@push('local.scripts')
    $('.sortable').sortable({
        handle: '.handle',
        start: function( event, ui ) { 
            $(ui.item).addClass('blue-grey lighten-4');
        },
        stop:function( event, ui ) { 
            $(ui.item).removeClass('blue-grey lighten-4');
        }
    })
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/jquery.ui.min.js?v='.config('system.version')) }}"></script>
@endpush
