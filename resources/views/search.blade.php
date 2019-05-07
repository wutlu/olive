@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Arama Motoru'
        ]
    ],
    'dock' => true,
    'wide' => true,
    'pin_group' => true
])

@push('local.styles')
    .marked {
        padding: .4rem;
        border-radius: .2rem;
    }

    #string {
        margin: 0;
        padding: 1rem;

                box-shadow: none !important;
        -webkit-box-shadow: none !important;

        border-width: 0;

        width: calc(100% - 2rem)
    }

    .owl-chart {
        position: relative;
    }
    .owl-chart .owl-wildcard-close {
        position: absolute;
        top: 0;
        right: 0;
    }
@endpush

@push('local.scripts')
    $(document).on('change', '[data-update]', function() {
        var search = $('ul#search');
            search.data('skip', 0).addClass('json-clear');

        vzAjax(search)
    }).on('click', '[data-update-click]', function() {
        var search = $('ul#search');
            search.data('skip', 0).addClass('json-clear');

        vzAjax(search)
    }).on('click', '.owl-wildcard-close', function() {
        var __ = $(this),
            owl = $('.owl-wildcard');

        owl.trigger('remove.owl.carousel', [__.closest('.owl-item').index()]).trigger('refresh.owl.carousel')

        var item_length = owl.find('.owl-item').length;

        if (item_length == 0)
        {
            $('#chart-area').addClass('hide')
        }
    })

    function __search_archive(__, obj)
    {
        var ul = $('ul#search');
            ul.closest('.card').removeClass('hide')

        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            $('[data-name=stats]').html('Yaklaşık ' + obj.stats.hits + ' sonuç bulundu (' + obj.stats.took + ' saniye)').removeClass('hide');

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp').attr('data-id', 'list-item-' + o.id)

                    var model;

                        switch(o._type)
                        {
                            case 'tweet'  : model = _tweet_  (o); break;
                            case 'entry'  : model = _entry_  (o); break;
                            case 'article': model = _article_(o); break;
                            case 'product': model = _product_(o); break;
                            case 'comment': model = _comment_(o); break;
                            case 'video'  : model = _video_  (o); break;
                        }

                        model.find('.text-area').mark(obj.words, {
                            'element': 'span',
                            'className': 'marked yellow black-text',
                            'accuracy': 'complementary'
                        })

                        item.html(model).appendTo(ul)
                })

                $('.dropdown-trigger').dropdown({
                    alignment: 'right'
                })
            }
        }

        $('#chart-area').addClass('hide')
    }

    $(window).on('load', function() {
        var input = $('input[name=string]');

        if (input.val().length)
        {
            vzAjax($('ul#search'))
            chip(input)
        }
    })

    function __aggregation(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#chart-area').removeClass('hide')

            $('#' + __.data('type') + '-chart').remove()

            $('.owl-wildcard').trigger('add.owl.carousel', [ $('<div />', {
                'class': 'owl-chart',
                'html': [
                    $('<canvas />', { 'id': __.data('type') + '-chart', 'height': '200' }),
                    $('<a />', {
                        'html': $('<i />', {
                            'class': 'material-icons',
                            'html': 'close'
                        }),
                        'href': '#',
                        'class': 'btn-floating btn-small red darken-2 owl-wildcard-close waves-effect'
                    })
                ]
            }), 0 ]).trigger('refresh.owl.carousel')

            if (__.data('type') == 'hourly')
            {
                var data = [];
                var option = histogramOption;
                    option['title']['text'] = 'SAATLİK İÇERİK GRAFİĞİ';

                $.each(obj.data.results, function(key, o) {
                    data.push(o.doc_count);
                })

                new Chart(document.getElementById(__.data('type') + '-chart'), {
                    type: 'bar',
                    data: {
                        labels: [ "00:00", "01:00", "02:00", "03:00", "04:00", "05:00", "06:00", "07:00", "08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00", "20:00", "21:00", "22:00", "23:00" ],
                        datasets: [
                            {
                                backgroundColor: '#0097a7',
                                data: data
                            }
                        ]
                    },
                    options: option
                })
            }
            else if (__.data('type') == 'daily')
            {
                var data = [
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0
                ];
                var option = histogramOption;
                    option['title']['text'] = 'GÜNLÜK İÇERİK GRAFİĞİ';

                $.each(obj.data.results, function(key, o) {
                    data[o.key - 1] = o.doc_count;
                })

                new Chart(document.getElementById(__.data('type') + '-chart'), {
                    type: 'bar',
                    data: {
                        labels: [ "Pazartesi", "Salı", "Çarşamba", "Perşembe", "Cuma", "Cumartesi", "Pazar" ],
                        datasets: [
                            {
                                backgroundColor: '#0097a7',
                                data: data
                            }
                        ]
                    },
                    options: option
                })
            }
            else if (__.data('type') == 'location')
            {
                var counts = [];
                var labels = [];
                var option = pieOption;
                    option['title']['text'] = 'KONUMA GÖRE İÇERİK GRAFİĞİ';

                $.each(obj.data.results, function(key, o) {
                    counts.push(o.doc_count);
                    labels.push(o.key);
                })

                new Chart(document.getElementById(__.data('type') + '-chart'), {
                    type: 'doughnut',
                    data: {
                        datasets: [
                            {
                                backgroundColor: [
                                    '#006064',
                                    '#00838f',
                                    '#0097a7',
                                    '#00acc1',
                                    '#00bcd4',
                                    '#26c6da',
                                    '#4dd0e1',
                                    '#80deea',
                                    '#b2ebf2',
                                    '#e0f7fa',
                                    '#006064',
                                    '#00838f',
                                    '#0097a7',
                                    '#00acc1',
                                    '#00bcd4',
                                    '#26c6da',
                                    '#4dd0e1',
                                    '#80deea',
                                    '#b2ebf2'
                                ],
                                data: counts
                            }
                        ],
                        labels: labels
                    },
                    options: option
                })
            }
            else if (__.data('type') == 'platform')
            {
                var counts = [];
                var labels = [];
                var option = pieOption;
                    option['title']['text'] = 'PLATFORMA GÖRE İÇERİK GRAFİĞİ';

                $.each(obj.data.results, function(key, o) {
                    counts.push(o.doc_count);
                    labels.push(o.key);
                })

                new Chart(document.getElementById(__.data('type') + '-chart'), {
                    type: 'doughnut',
                    data: {
                        datasets: [
                            {
                                backgroundColor: [
                                    '#006064',
                                    '#00838f',
                                    '#0097a7',
                                    '#00acc1',
                                    '#00bcd4',
                                    '#26c6da',
                                    '#4dd0e1',
                                    '#80deea',
                                    '#b2ebf2',
                                    '#e0f7fa',
                                    '#006064',
                                    '#00838f',
                                    '#0097a7',
                                    '#00acc1',
                                    '#00bcd4',
                                    '#26c6da',
                                    '#4dd0e1',
                                    '#80deea',
                                    '#b2ebf2'
                                ],
                                data: counts
                            }
                        ],
                        labels: labels
                    },
                    options: option
                })
            }
            else if (__.data('type') == 'hashtag')
            {
                var counts = [];
                var labels = [];
                var option = pieOption;
                    option['title']['text'] = 'HASHTAG GRAFİĞİ';

                $.each(obj.data.results, function(key, o) {
                    counts.push(o.doc_count);
                    labels.push(o.key);
                })

                new Chart(document.getElementById(__.data('type') + '-chart'), {
                    type: 'doughnut',
                    data: {
                        datasets: [
                            {
                                backgroundColor: [
                                    '#006064',
                                    '#00838f',
                                    '#0097a7',
                                    '#00acc1',
                                    '#00bcd4',
                                    '#26c6da',
                                    '#4dd0e1',
                                    '#80deea',
                                    '#b2ebf2',
                                    '#e0f7fa',
                                    '#006064',
                                    '#00838f',
                                    '#0097a7',
                                    '#00acc1',
                                    '#00bcd4',
                                    '#26c6da',
                                    '#4dd0e1',
                                    '#80deea',
                                    '#b2ebf2'
                                ],
                                data: counts
                            }
                        ],
                        labels: labels
                    },
                    options: option
                })
            }
            else if (__.data('type') == 'source')
            {
                var counts = [];
                var labels = [];
                var option = pieOption;
                    option['title']['text'] = 'KAYNAĞINA GÖRE İÇERİK GRAFİĞİ';

                $.each(obj.data, function(key, item) {
                    counts.push(item);
                    labels.push(key);
                })

                new Chart(document.getElementById(__.data('type') + '-chart'), {
                    type: 'doughnut',
                    data: {
                        datasets: [
                            {
                                backgroundColor: [
                                    '#006064',
                                    '#00838f',
                                    '#0097a7',
                                    '#00acc1',
                                    '#00bcd4',
                                    '#26c6da',
                                    '#4dd0e1',
                                    '#80deea',
                                    '#b2ebf2',
                                    '#e0f7fa',
                                    '#006064',
                                    '#00838f',
                                    '#0097a7',
                                    '#00acc1',
                                    '#00bcd4',
                                    '#26c6da',
                                    '#4dd0e1',
                                    '#80deea',
                                    '#b2ebf2'
                                ],
                                data: counts
                            }
                        ],
                        labels: labels
                    },
                    options: option
                })
            }
            else if (__.data('type') == 'sentiment')
            {
                var counts = [];
                var labels = [];
                var option = pieOptionPercentable;
                    option['title']['text'] = 'DUYGU GRAFİĞİ';

                var readable_labels = {
                    'neutral': 'Nötr',
                    'negative': 'Negatif',
                    'positive': 'Pozitif'
                };

                $.each(obj.data.results, function(key, item) {
                    counts.push(item.value);
                    labels.push(readable_labels[key]);
                })

                new Chart(document.getElementById(__.data('type') + '-chart'), {
                    type: 'doughnut',
                    data: {
                        datasets: [
                            {
                                backgroundColor: [
                                    '#e53935',
                                    '#bdbdbd',
                                    '#0097a7'
                                ],
                                data: counts
                            }
                        ],
                        labels: labels
                    },
                    options: option
                })
            }
            else if (__.data('type') == 'mention')
            {
                $('#' + __.data('type') + '-chart').remove()

                $.each(obj.data, function(key, set) {
                    $('#' + key + '-list').closest('.owl-item').remove()

                    var collection = $('<ul />', {
                        'class': 'collection collection-small',
                        'id': key + '-list'
                    });

                    var title = '';

                    switch(key)
                    {
                        case 'twitter':           title = 'Twitter (bu hesaplar konuştu)';            break;
                        case 'twitter_out':       title = 'Twitter (bu hesaplar konuşuldu)';          break;
                        case 'news':              title = 'Haber (bu siteler haber yaptı)';           break;
                        case 'youtube_comment':   title = 'YouTube (bu kanallar yorum yaptı)';        break;
                        case 'youtube_video':     title = 'YouTube (bu kanallar video paylaştı)';     break;
                        case 'sozluk':            title = 'Sözlük (bu kişiler entry girdi)';          break;
                        case 'shopping':          title = 'E-ticaret (bu sitelerde ürün paylaşıldı)'; break;
                    }

                    $.each(set, function(k, item) {
                        var value = '';

                        switch(key)
                        {
                            case 'twitter':
                            case 'twitter_out':
                                value = $('<a />', {
                                    'html': item['name'],
                                    'href': '#',
                                    'data-search': '@' + item['screen_name'],
                                    'class': 'red-text'
                                });
                            break;
                            case 'news':
                            case 'shopping':
                                value = $('<a />', {
                                    'html': item['name'],
                                    'href': '#',
                                    'data-search': 'site_id:' + item['key'],
                                    'class': 'green-text'
                                });
                            break;
                            case 'youtube_comment':
                            case 'youtube_video':
                                value = $('<a />', {
                                    'html': item['title'],
                                    'href': '#',
                                    'data-search': 'channel.id:' + item['key'],
                                    'class': 'red-text'
                                });
                            break;
                            case 'sozluk':
                                value = $('<a />', {
                                    'html': item['key'],
                                    'href': '#',
                                    'data-search': 'author:' + '"' + item['key'] + '"',
                                    'class': 'red-text'
                                });
                            break;
                        }

                        var collection_item = $('<li />', {
                            'class': 'collection-item',
                            'html': [
                                value,
                                $('<span />', {
                                    'class': 'badge grey lighten-5',
                                    'html': item['doc_count']
                                })
                            ]
                        })

                        collection_item.appendTo(collection)
                    })

                    $('.owl-wildcard').trigger('add.owl.carousel', [$('<div />', {
                        'class': 'owl-list',
                        'css': {
                            'width': '500px',
                            'height': '200px',
                            'overflow': 'auto'
                        },
                        'html': [
                            $('<span />', {
                                'class': 'pl-1 lr-1 teal-text',
                                'html': title
                            }),
                            collection
                        ]
                    }), 0]).trigger('refresh.owl.carousel')
                })
            }
        }
    }

    var pieOptionPercentable = {
        title: {
            display: true
        },
        legend: {
            display: true,
            position: 'right'
        },
        maintainAspectRatio: false,
        tooltips: {
            callbacks: {
                label: function(tooltipItem, data) {
                    var dataset = data.datasets[tooltipItem.datasetIndex];
                    var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                        return previousValue + currentValue;
                    });

                    var currentValue = dataset.data[tooltipItem.index];
                    var percentage = Math.floor(((currentValue/total) * 100)+0.5);

                    return percentage + '%';
                }
            }
        }
    };

    var pieOption = {
        title: {
            display: true
        },
        legend: {
            display: true,
            position: 'right'
        },
        maintainAspectRatio: false
    };

    var histogramOption = {
        title: {
            display: true
        },
        legend: { display: false },
        layout: {
            padding: {
                top: 20,
                right: 20,
                bottom: 20,
                left: 20
            }
        },
        maintainAspectRatio: false
    };

    var barOption = {
        title: {
            display: true
        },
        legend: { display: false },
        layout: {
            padding: {
                top: 20,
                right: 20,
                bottom: 20,
                left: 20
            }
        },
        maintainAspectRatio: false
    };

    $('.datepicker').datepicker({
        firstDay: 0,
        format: 'dd.mm.yyyy',
        i18n: date.i18n,
        container: 'body'
    })

    $('.owl-wildcard').owlCarousel({
        responsiveClass: true,
        autoWidth: true,
        dotClass: 'hide',
        singleItem: true,
        navText: [
            '<div class="nav-btn prev-slide d-flex"><i class="material-icons align-self-center">keyboard_arrow_left</i></div>',
            '<div class="nav-btn next-slide d-flex"><i class="material-icons align-self-center">keyboard_arrow_right</i></div>'
        ],
        nav: true
    })

    $('.owl-chips').owlCarousel({
        responsiveClass: true,
        autoWidth: true,
        dotClass: 'hide',
        navText: [
            '<div class="nav-btn prev-slide d-flex"><i class="material-icons align-self-center">keyboard_arrow_left</i></div>',
            '<div class="nav-btn next-slide d-flex"><i class="material-icons align-self-center">keyboard_arrow_right</i></div>'
        ],
        nav: true
    })

    function __chart(parent, data)
    {
        var chart = $('<canvas />', {
            'width': '64',
            'height': '24',
            'data-name': 'chart'
        })

        parent.html(chart)

        setTimeout(function() {
            new Chart(chart, {
                type: 'bar',
                data: data,
                options: options
            })
        }, 100)
    }

    $(document).on('click', '[data-search]', function() {
        var input = $('input[name=string]'),
            search = $('ul#search');

            input.val(input.val() + ' ' + $(this).data('search'))

            search.data('skip', 0).addClass('json-clear')

        setTimeout(function() {
            vzAjax(search)
        }, 400)
    }).on('keyup', 'input[name=string]', function(e) {
        var __ = $(this),
            keycode = (e.keyCode ? e.keyCode : e.which);

        if (keycode == '13')
        {
            chip(__)
        }
    })

    function chip(__)
    {
        var id = hashCode(__.val());

        if (!$('.chip-s').find('.chip[data-id=' + id + ']').length && __.val().length)
        {
            $('.owl-chips').trigger('add.owl.carousel', [ $('<a />', {
                'href': '#',
                'class': 'chip waves-effect indigo white-text mb-0',
                'data-search': __.val(),
                'html': __.val(),
                'data-id': id
            }), 0 ]).trigger('refresh.owl.carousel')
        }
    }
@endpush

@push('local.styles')
    .owl-chart {
        width: 100%;
        height: 200px;
    }

    .owl-chart > #daily-chart,
    .owl-chart > #hourly-chart {
        min-width: 600px;
    }
@endpush

@section('content')
    <div class="card hide">
        <div class="time-line">
            <ul class="collection json-clear loading" 
                id="search"
                data-href="{{ route('search.dashboard') }}"
                data-skip="0"
                data-more-button="#search-more_button"
                data-callback="__search_archive"
                data-method="post"
                data-include="start_date,end_date,sentiment,modules,string,sort,retweet,verified,take,media"
                data-nothing>
                <li class="collection-item nothing hide">
                    @component('components.nothing')
                        @slot('size', 'small')
                        @slot('text', 'Sonuç bulunamadı!')
                    @endcomponent
                </li>
                <li class="collection-item model hide"></li>
            </ul>
        </div>
    </div>

    <a href="#"
       class="more hide json"
       id="search-more_button"
       data-json-target="#search">Daha Fazla</a>
@endsection

@include('_inc.alerts.search_operators')

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/chart.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.mark.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
    <script src="{{ asset('js/owl.carousel.min.js?v='.config('system.version')) }}"></script>
@endpush

@section('panel')
    <div class="card mb-1">
        <div class="card-image">
            <img src="{{ asset('img/md/21.jpg') }}" alt="Image" />
            <span class="card-title white-text d-flex">
                <i class="material-icons align-self-center mr-1">date_range</i>
                Tarih
            </span>
        </div>
        <div class="card-content">
            <input data-update type="date" class="d-block" name="start_date" value="{{ $s ? $s : date('Y-m-d', strtotime('-1 day')) }}" placeholder="Başlangıç" />
            <input data-update type="date" class="d-block" name="end_date" value="{{ $e ? $e : date('Y-m-d') }}" placeholder="Bitiş" />
        </div>
        <div class="collection">
            <a
                href="#"
                data-update-click
                class="collection-item waves-effect"
                data-input="input[name=end_date]"
                data-focus="input[name=start_date]"
                data-input-value="{{ date('Y-m-d') }}"
                data-value="{{ date('Y-m-d') }}">Bugün</a>
            @if ($organisation->historical_days >= 1)
                <a
                    href="#"
                    data-update-click
                    class="collection-item waves-effect"
                    data-input="input[name=end_date]"
                    data-focus="input[name=start_date]"
                    data-input-value="{{ date('Y-m-d', strtotime('-1 day')) }}"
                    data-value="{{ date('Y-m-d', strtotime('-1 day')) }}">Dün</a>
            @endif
            @if ($organisation->historical_days >= 2)
                <a
                    href="#"
                    data-update-click
                    class="collection-item waves-effect"
                    data-input="input[name=end_date]"
                    data-focus="input[name=start_date]"
                    data-input-value="{{ date('Y-m-d') }}"
                    data-value="{{ date('Y-m-d', strtotime('-1 day')) }}">Son 2 Gün</a>
            @endif
            @if ($organisation->historical_days >= 7)
                <a
                    href="#"
                    data-update-click
                    class="collection-item waves-effect"
                    data-input="input[name=end_date]"
                    data-focus="input[name=start_date]"
                    data-input-value="{{ date('Y-m-d') }}"
                    data-value="{{ date('Y-m-d', strtotime('-7 day')) }}">Son 7 Gün</a>
            @endif
            @if ($organisation->historical_days >= 30)
                <a
                    href="#"
                    data-update-click
                    class="collection-item waves-effect"
                    data-input="input[name=end_date]"
                    data-focus="input[name=start_date]"
                    data-input-value="{{ date('Y-m-d') }}"
                    data-value="{{ date('Y-m-d', strtotime('-30 day')) }}">Son 30 Gün</a>
            @endif
            @if ($organisation->historical_days >= 90)
                <a
                    href="#"
                    data-update-click
                    class="collection-item waves-effect"
                    data-input="input[name=end_date]"
                    data-focus="input[name=start_date]"
                    data-input-value="{{ date('Y-m-d') }}"
                    data-value="{{ date('Y-m-d', strtotime('-90 day')) }}">Son 90 Gün</a>
            @endif
        </div>
    </div>
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/md/21.jpg') }}" alt="Image" />
            <span class="card-title white-text d-flex">
                <i class="material-icons align-self-center mr-1">grain</i>
                Sayfa
            </span>
        </div>
        <div class="card-content">
            <div class="input-field">
                <select name="take" id="take" data-update>
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <label>Sayfalama</label>
            </div>
        </div>
    </div>
@endsection

@push('local.scripts')
    $('select').formSelect()
@endpush

@section('wildcard')
    <div class="wild-area z-depth-1">
        <div class="wild-content d-flex" data-wild="sentiment">
            <span class="wild-body d-flex">
                <a href="#" class="btn-floating btn-flat btn-small waves-effect align-self-center" data-class=".wild-content" data-class-remove="active" style="margin: 0 .4rem 0 0;">
                    <i class="material-icons">close</i>
                </a>
                <label class="align-self-center mr-1" data-tooltip="Pozitif">
                    <input data-update type="radio" name="sentiment" value="pos" />
                    <span class="material-icons green-text">sentiment_very_satisfied</span>
                </label>
                <label class="align-self-center mr-1" data-tooltip="Nötr">
                    <input data-update type="radio" name="sentiment" value="neu" />
                    <span class="material-icons grey-text text-darken-2">sentiment_neutral</span>
                </label>
                <label class="align-self-center mr-1" data-tooltip="Negatif">
                    <input data-update type="radio" name="sentiment" value="neg" />
                    <span class="material-icons red-text">sentiment_very_dissatisfied</span>
                </label>
                <label class="align-self-center mr-1" data-tooltip="Tümü">
                    <input data-update type="radio" name="sentiment" value="all" checked="" />
                    <span class="material-icons grey-text text-darken-2">fullscreen</span>
                </label>
            </span>
        </div>
        <div class="wild-content d-flex" data-wild="lists">
            <span class="wild-body d-flex">
                <a href="#" class="btn-floating btn-flat btn-small waves-effect align-self-center" data-class=".wild-content" data-class-remove="active" style="margin: 0 .4rem 0 0;">
                    <i class="material-icons">close</i>
                </a>
                <button type="button" data-type="mention" data-tooltip="Kimler Bahsetti?" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string,modules,verified,retweet,media" data-href="{{ route('search.aggregation') }}" data-method="post" class="btn-flat btn-small json waves-effect align-self-center loading" style="margin: 0 .2rem 0 0;">@</button>
                <button type="button" data-type="hashtag" data-tooltip="Hangi Hashtagler Kullanıldı?" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string,modules,verified,retweet,media" data-href="{{ route('search.aggregation') }}" data-method="post" class="btn-flat btn-small json waves-effect align-self-center loading" style="margin: 0 .2rem 0 0;">#</button>
                <button type="button" data-type="source" data-tooltip="Kaynak Grafiği" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string,modules,verified,retweet,media" data-href="{{ route('search.aggregation') }}" data-method="post" class="btn-flat btn-small json waves-effect align-self-center loading" style="margin: 0 .2rem 0 0;">Kaynaklar</button>
            </span>
        </div>
        <div class="wild-content d-flex" data-wild="graph">
            <span class="wild-body d-flex">
                <a href="#" class="btn-floating btn-flat btn-small waves-effect align-self-center" data-class=".wild-content" data-class-remove="active" style="margin: 0 .4rem 0 0;">
                    <i class="material-icons">close</i>
                </a>
                <button type="button" data-type="hourly" data-tooltip="Saatlik İçerik Grafiği" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string,modules,verified,retweet,media" data-href="{{ route('search.aggregation') }}" data-method="post" class="btn-flat btn-small json waves-effect align-self-center loading" style="margin: 0 .2rem 0 0;">S</button>
                <button type="button" data-type="daily" data-tooltip="Günlük İçerik Grafiği" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string,modules,verified,retweet,media" data-href="{{ route('search.aggregation') }}" data-method="post" class="btn-flat btn-small json waves-effect align-self-center loading" style="margin: 0 .2rem 0 0;">G</button>
                <button type="button" data-type="location" data-tooltip="Konum Grafiği" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string,modules,verified,retweet,media" data-href="{{ route('search.aggregation') }}" data-method="post" class="btn-flat btn-small json waves-effect align-self-center loading" style="margin: 0 .2rem 0 0;">
                    <i class="material-icons">location_on</i>
                </button>
                <button type="button" data-type="platform" data-tooltip="Platform Grafiği" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string,modules,verified,retweet,media" data-href="{{ route('search.aggregation') }}" data-method="post" class="btn-flat btn-small json waves-effect align-self-center loading" style="margin: 0 .2rem 0 0;">
                    <i class="material-icons">devices</i>
                </button>
                <button type="button" data-type="sentiment" data-tooltip="Duygu Grafiği" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string,modules,verified,retweet,media" data-href="{{ route('search.aggregation') }}" data-method="post" class="btn-flat btn-small json waves-effect align-self-center loading" style="margin: 0 .2rem 0 0;">
                    <i class="material-icons">sentiment_satisfied</i>
                </button>
            </span>
        </div>
        <div class="wild-content d-flex" data-wild="settings">
            <span class="wild-body d-flex">
                <a href="#" class="btn-floating btn-flat btn-small waves-effect align-self-center" data-class=".wild-content" data-class-remove="active" style="margin: 0 .4rem 0 0;">
                    <i class="material-icons">close</i>
                </a>
                <label class="align-self-center mr-1">
                    <input data-update type="checkbox" name="sort" value="asc" />
                    <span class="grey-text text-darken-2">İlk İçerikler</span>
                </label>
            </span>
        </div>
        <ul class="wild-menu">
            <li>
                <a class="d-flex" href="#" data-class="[data-wild=sentiment]" data-class-add="active">
                    <i class="material-icons">mood</i>
                </a>
            </li>
            <li>
                <a class="d-flex" href="#" data-class="[data-wild=graph]" data-class-add="active">
                    <i class="material-icons">show_chart</i>
                </a>
            </li>
            <li>
                <a class="d-flex" href="#" data-class="[data-wild=lists]" data-class-add="active">
                    <i class="material-icons">people</i>
                </a>
            </li>
            <li>
                <a class="d-flex" href="#" data-class="[data-wild=settings]" data-class-add="active">
                    <i class="material-icons">settings</i>
                </a>
            </li>
            <li>
                <a class="d-flex" href="#" data-trigger="info">
                    <i class="material-icons">info_outline</i>
                </a>
            </li>
        </ul>
    </div>
    <input
        class="validate json json-search grey lighten-4"
        id="string"
        name="string"
        type="text"
        data-json-target="ul#search"
        placeholder="Ara"
        value="{{ $q }}" />
    <div class="grey lighten-4 grey-text p-1 hide" data-name="stats"></div>
    <div class="chip-s owl-chips owl-carousel grey lighten-4 z-depth-1">
        @if (@$trends)
            @foreach ($trends as $trend)
                <a class="chip cyan darken-2 white-text waves-effect mb-0" data-search="{{ $trend->title }}" href="#">{{ $trend->title }}</a>
            @endforeach
        @endif
    </div>
    <div class="z-depth-1 hide" id="chart-area">
        <div class="owl-carousel owl-wildcard"></div>
    </div>
@endsection

@section('dock')
    <div class="card with-bg mb-1">
        <div class="card-image">
            <img src="{{ asset('img/md-s/21.jpg') }}" alt="Image" />
            <span class="card-title white-text d-flex">
                <i class="material-icons align-self-center mr-1">device_hub</i>
                Kaynak
            </span>
        </div>
        <div class="collection collection-bordered">
            @foreach (config('system.modules') as $key => $module)
                @if ($organisation->{'data_'.$key})
                    @if ($key == 'twitter')
                        <label class="collection-item waves-effect d-block">
                            <input data-update name="modules" checked value="{{ $key }}" data-multiple="true" type="checkbox" />
                            <span>{{ $module }}</span>
                        </label>
                        <div class="collection-item teal lighten-5">
                            <label>
                                <input data-update type="radio" name="retweet" value="all" />
                                <span class="grey-text text-darken-2 align-self-center">Tümü <sup>RT Dahil</sup></span>
                            </label>
                            <label>
                                <input data-update type="radio" name="retweet" value="tweet" checked />
                                <span class="grey-text text-darken-2 align-self-center">Sadece Tweet</span>
                            </label>
                            <label>
                                <input data-update type="radio" name="retweet" value="quote" />
                                <span class="grey-text text-darken-2 align-self-center">Sadece Alıntılar</span>
                            </label>
                            <label>
                                <input data-update type="radio" name="retweet" value="reply" />
                                <span class="grey-text text-darken-2 align-self-center">Sadece Cevaplar</span>
                            </label>

                            <hr class="teal lighten-4 mt-1 mb-1" />

                            <label>
                                <input data-update type="checkbox" name="verified" value="on" />
                                <span class="grey-text text-darken-2 align-self-center">Doğrulanmış Hesaplar</span>
                            </label>

                            <hr class="teal lighten-4 mt-1 mb-1" />

                            <label>
                                <input data-update type="checkbox" name="media" value="on" />
                                <span class="grey-text text-darken-2 align-self-center">Medyalı Tweetler</span>
                            </label>
                        </div>
                    @else
                        <label class="collection-item waves-effect d-block">
                            <input data-update name="modules" checked value="{{ $key }}" data-multiple="true" type="checkbox" />
                            <span>{{ $module }}</span>
                        </label>
                    @endif
                @endif
            @endforeach
        </div>
    </div>
@endsection
