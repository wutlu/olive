@extends('layouts.app', [
    'sidenav_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Trend Analizi'
        ],
        [
            'text' => 'Canlı Trend'
        ]
    ],
    'footer_hide' => true,
    'wide' => true,
    'help' => 'driver.start()',
    'dock' => true
])

@section('dock')
    <div class="right-align pb-1">
        <a href="#" class="btn-flat btn-floating waves-effect" data-trigger="full-screen">
            <i class="material-icons">fullscreen</i>
        </a>
    </div>

    @include('trends._menu', [ 'active' => 'live' ])

    <div class="card card-unstyled mt-1">
        <table>
            <tbody>
                <tr>
                    <td class="right-align">Ekrana Düşen Trend</td>
                    <td>
                        <span class="badge white grey-text text-darken-2" data-name="incoming-trends">0</span>
                    </td>
                </tr>
                <tr>
                    <td class="right-align">Ekrandan Çıkan Trend</td>
                    <td>
                        <span class="badge white grey-text text-darken-2" data-name="outbound-trends">0</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="card card-unstyled mt-1">
        <ul>
            <li>
                <small class="d-table red-text">DÜŞÜŞTE</small>
            </li>
            <li>
                <small class="d-table green-text">YÜKSELİŞTE</small>
            </li>
            <li>
                <small class="d-table blue-text">YENİ</small>
            </li>
            <li>
                <small class="d-table grey-text">YERİNİ KORUYOR</small>
            </li>
            <li>
                <small class="d-table red lighten-4">OLAĞANÜSTÜ BİR HAREKETLİLİK</small>
            </li>
        </ul>
    </div>
@endsection

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
            helpStart.reset()

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

                        item.find('[data-name=hit]').html(number_format(o.hit))

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
                        else if (__.data('module') == 'twitter_favorite')
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

    $(document).keydown(function(e) {
        if (e.keyCode == 27)
        {
            $('body').removeClass('full-screen')
        }
    }).on('click', '[data-trigger=full-screen]', function() {
        $('body').addClass('full-screen')

        var docElm = document.documentElement;

        if (docElm.requestFullscreen)
        {
            docElm.requestFullscreen();
        }
        else if (docElm.mozRequestFullScreen)
        {
            docElm.mozRequestFullScreen();
        }
        else if (docElm.webkitRequestFullScreen)
        {
            docElm.webkitRequestFullScreen();
        }

        modal({
            'id': 'info',
            'body': 'Tam ekran modundan çıkmak için ESC tuşunu kullanın.',
            'size': 'modal-small',
            'title': keywords.info,
            'options': {},
            'footer': [
               $('<a />', {
                   'href': '#',
                   'class': 'modal-close waves-effect btn-flat',
                   'html': keywords.ok
               })
            ]
        })
    })
@endpush

@push('local.styles')
    [data-id=trend_list] {
        min-height: 200px;
        max-height: calc(100vh);
        overflow: auto;
        background-image: url(../img/8vz.net_logo-opacity.svg);
        background-repeat: no-repeat;
        background-position: center;
        background-size: 50%;
    }

    .image.verified {
        -webkit-box-shadow: 0 0 0 4px #bbdefb;
                box-shadow: 0 0 0 4px #bbdefb;
    }

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

    @media only screen and (max-width: 1600px) {
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

    body.full-screen {
        overflow: hidden;
    }
    body.full-screen [data-full-screen] {
        position: fixed;
        z-index: 999;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background-color: #f0f0f0;
        overflow: auto;
    }
@endpush

@section('content')
    <div class="card-deck sortable" data-full-screen>
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
                    class="collapsible resizable"
                    data-href="{{ route('trend.live') }}"
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
                                            <span class="align-self-center">8vz'de göster</span>
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
@endsection

@push('local.scripts')
    $('.sortable').sortable({
        handle: '.handle',
        start: function( event, ui ) { 
            $(ui.item).addClass('blue-grey lighten-4');
        },
        stop:function( event, ui ) { 
            $(ui.item).removeClass('blue-grey lighten-4');
        },
        placeholder: 'card'
    })

    const helpStart = new Driver({
        allowClose: false,
        showButtons: false,
        keyboardControl: false,
        padding: 16,
        onReset: function() {
            @if (!auth()->user()->intro('driver.trend'))
                vzAjax($('<div />', {
                    'class': 'json',
                    'data-method': 'post',
                    'data-href': '{{ route('intro', 'driver.trend') }}'
                }))
            @endif
        }
    })

    helpStart.defineSteps([
        {
            element: '.play-btn',
            popover: {
                title: 'Hadi Başlayalım!',
                description: 'İlgilendiğiniz grubu başlatın ve trendlerini canlı olarak izleyin.',
                position: 'left'
            }
        }
    ])

    const driver = new Driver({
        allowClose: false,
        padding: 6,
        onReset: function() {
            setTimeout(function() {
                helpStart.start()
            }, 400)
        }
    })

    driver.defineSteps([
        {
            element: '.drag-btn',
            popover: {
                title: 'Önce Sıralama',
                description: 'Grupları sürükleyerek istediğiniz şekilde sıralayın.'
            }
        },
        {
            element: '#card-twitter_tweet',
            popover: {
                title: 'Twitter, Tweet',
                description: 'Son 10 dakika içerisinde paylaşılan Türkçe Tweetler arasında en çok etkileşim alan Tweetler bu grupta sıralanır.'
            }
        },
        {
            element: '#card-twitter_favorite',
            popover: {
                title: 'Twitter, Favori',
                description: 'Son 1 saat içerisinde paylaşılan Türkçe Tweetler arasında en çok beğenilen Tweetler bu grupta sıralanır.'
            }
        },
        {
            element: '#card-twitter_hashtag',
            popover: {
                title: 'Twitter, Hashtag',
                description: 'Son 10 dakika içerisinde paylaşılan ve en az 1 #hashtag içeren Türkçe Tweetler içerisinde en çok kullanılan hashtagler bu grupta sıralanır.'
            }
        },
        {
            element: '#card-news',
            popover: {
                title: 'Medya, Haber',
                description: 'Tüm haber sitelerinin son 10 dakika içerisinde paylaştığı haberler benzerlik oranlarına göre gruplanır ve sitenin Alexa değerleri göz önünde bulundurularak içerikler bu grupta sıralanır.'
            }
        },
        {
            element: '#card-entry',
            popover: {
                title: 'Sözlük, Entry',
                description: 'Sözlüklerde, açılan başlıkların aldığı cevaplara göre son 10 dakika içerisinde en çok cevap alan başlıklar bu grupta sıralanır.'
            }
        },
        {
            element: '#card-youtube_video',
            popover: {
                title: 'YouTube, Video',
                description: 'Son 1 saat içerisinde yapılan yorumlara göre Türkçe videolar bu grupta sıralanır.'
            }
        },
        {
            element: '#card-google',
            popover: {
                title: 'Google',
                description: 'Şu an Türkiye, Google\'da neler arıyor? Bu verileri dinamikleştirip bu grupta sıralıyoruz. Tabii ki de son 1 saat.'
            }
        },
        {
            element: '#card-blog',
            popover: {
                title: 'Blog, Makale',
                description: 'Tüm blog sitelerinin son 10 dakika içerisinde paylaştığı bloglar benzerlik oranlarına göre gruplanır ve sitenin Alexa değerleri göz önünde bulundurularak içerikler bu grupta sıralanır.'
            }
        },
        {
            element: '#card-instagram_hashtag',
            popover: {
                title: 'Instagram, Hashtag',
                description: 'Son 10 dakika içerisinde paylaşılan ve en az 1 #hashtag içeren Türkçe medyalar içerisinde en çok kullanılan hashtagler bu grupta sıralanır.'
            }
        }
    ])

    @if (!auth()->user()->intro('driver.trend'))
        driver.start()
    @endif
@endpush
