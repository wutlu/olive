@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Arama Motoru'
        ]
    ],
    'dock' => true,
    'wide' => true
])

@push('local.styles')
    .marked {
        padding: .4rem;
        border-radius: .2rem;
    }

    .time-line > .collection > .collection-item {
        word-break: break-word;
        padding: 2rem;

    }
    .time-line > .collection > .collection-item .title {
        font-size: 16px;
    }

    .search-field {
        padding: 1rem 0;
    }

    .search-field #string {
                transition: all 400ms cubic-bezier(0.25, 0.46, 0.45, 0.94);
        -webkit-transition: all 400ms cubic-bezier(0.25, 0.46, 0.45, 0.94);

        margin: 0;
        padding: 0 1rem;
        border-width: 0;

                box-shadow: .1rem .1rem .2rem 0 rgba(0, 0, 0, .1);
        -webkit-box-shadow: .1rem .1rem .2rem 0 rgba(0, 0, 0, .1);

        width: calc(100% - 2rem);
    }
    .search-field #string:focus {
                box-shadow: .1rem .1rem 1rem 0 rgba(0, 0, 0, .1);
        -webkit-box-shadow: .1rem .1rem 1rem 0 rgba(0, 0, 0, .1);
    }
@endpush

@push('local.scripts')
    var group_select = $('select[name=group_id]');
        group_select.formSelect()

    function __pin(__, obj)
    {
        if (obj.status == 'removed')
        {
            $('[data-pin-uuid=' + __.attr('data-pin-uuid') + ']').removeClass('on')

            M.toast({ html: 'Pin Kaldırıldı', classes: 'red darken-2' })
        }
        else if (obj.status == 'pinned')
        {
            $('[data-pin-uuid=' + __.attr('data-pin-uuid') + ']').addClass('on')

            var toastHTML = $('<div />', {
                'html': [
                    $('<span />', {
                        'html': 'İçerik Pinlendi',
                        'class': 'white-text'
                    }),
                    $('<a />', {
                        'href': '#',
                        'class': 'btn-flat toast-action json',
                        'html': 'Geri Al',
                        'data-undo': 'true',
                        'data-href': '{{ route('pin', 'remove') }}',
                        'data-method': 'post',
                        'data-callback': '__pin',
                        'data-id': __.data('id'),
                        'data-type': __.data('type'),
                        'data-index': __.data('index'),
                        'data-pin-uuid': __.data('pin-uuid'),
                        'data-include': 'group_id'
                    })
                ]
            });

            M.toast({ html: toastHTML.get(0).outerHTML })
        }
        else if (obj.status == 'failed')
        {
            M.toast({ html: 'Hay aksi, beklenmedik bir durum.', classes: 'orange darken-2' })
        }
    }

    function __pin_group(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'Grup Seçildi' })
        }
    }

    function __search_archive(__, obj)
    {
        var ul = $('ul#search');
        var item_model = ul.children('.model');

            ul.closest('.card').removeClass('hide')

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            $('[data-name=stats]').html('Yaklaşık ' + obj.stats.hits + ' sonuç bulundu (' + obj.stats.took + ' saniye)');

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp').attr('data-id', 'list-item-' + o.id)

                        switch(o._type)
                        {
                            case 'tweet':
                                var model = $('<div />', {
                                    'html': [
                                        $('<div />', {
                                            'html': [
                                                $('<a />', {
                                                    'html': o.user.name,
                                                    'href': 'https://twitter.com/' + o.user.screen_name,
                                                    'class': 'd-table red-text'
                                                }).attr('target', 'blank'),
                                                $('<time>', {
                                                    'html': o.created_at,
                                                    'class': 'd-table grey-text text-lighten-1'
                                                }),
                                                $('<span />', {
                                                    'html': o.text,
                                                    'class': 'grey-text text-darken-2'
                                                }),
                                                $('<a />', {
                                                    'html': 'https://twitter.com/' + o.user.screen_name + '/status/' + o._id,
                                                    'href': 'https://twitter.com/' + o.user.screen_name + '/status/' + o._id,
                                                    'class': 'd-table green-text'
                                                }).attr('target', '_blank')
                                            ]
                                        })
                                    ]
                                }).mark(obj.words, {
                                    'element': 'span',
                                    'className': 'marked yellow black-text',
                                    'accuracy': 'complementary'
                                });

                                if (o.deleted_at)
                                {
                                    model.css({ 'opacity': '.4' })
                                }
                            break;
                            case 'entry':
                                var model = $('<div />', {
                                    'html': [
                                        $('<div />', {
                                            'html': [
                                                $('<span />', {
                                                    'html': o.title,
                                                    'class': 'd-table blue-text title'
                                                }),
                                                $('<span />', {
                                                    'html': o.author,
                                                    'class': 'd-table red-text'
                                                }),
                                                $('<time>', {
                                                    'html': o.created_at,
                                                    'class': 'd-table grey-text text-lighten-1'
                                                }),
                                                $('<span />', {
                                                    'html': o.text,
                                                    'class': 'grey-text text-darken-2'
                                                }),
                                                $('<a />', {
                                                    'html': o.url,
                                                    'href': o.url,
                                                    'class': 'd-table green-text'
                                                }).attr('target', '_blank')
                                            ]
                                        })
                                    ]
                                }).mark(obj.words, {
                                    'element': 'span',
                                    'className': 'marked yellow black-text',
                                    'accuracy': 'complementary'
                                });

                                if (o.deleted_at)
                                {
                                    model.css({ 'opacity': '.4' })
                                }
                            break;
                            case 'article':
                                var model = $('<div />', {
                                    'html': [
                                        $('<div />', {
                                            'html': [
                                                $('<span />', {
                                                    'html': o.title,
                                                    'class': 'd-table blue-text title'
                                                }),
                                                $('<time>', {
                                                    'html': o.created_at,
                                                    'class': 'd-table grey-text text-lighten-1'
                                                }),
                                                $('<span />', {
                                                    'html': o.text,
                                                    'class': 'grey-text text-darken-2'
                                                }),
                                                $('<a />', {
                                                    'html': str_limit(o.url, 96),
                                                    'href': o.url,
                                                    'class': 'd-table green-text'
                                                }).attr('target', '_blank')
                                            ]
                                        })
                                    ]
                                }).mark(obj.words, {
                                    'element': 'span',
                                    'className': 'marked yellow black-text',
                                    'accuracy': 'complementary'
                                });

                                if (o.deleted_at)
                                {
                                    model.css({ 'opacity': '.4' })
                                }
                            break;
                            case 'product':
                                var model = $('<div />', {
                                    'html': [
                                        $('<div />', {
                                            'html': [
                                                $('<span />', {
                                                    'html': o.title,
                                                    'class': 'd-table blue-text title'
                                                }),
                                                $('<time>', {
                                                    'html': o.created_at,
                                                    'class': 'd-table grey-text text-lighten-1'
                                                }),
                                                $('<span />', {
                                                    'html': o.text ? o.text : 'Açıklama Yok',
                                                    'class': 'grey-text text-darken-2'
                                                }),
                                                $('<a />', {
                                                    'html': str_limit(o.url, 96),
                                                    'href': o.url,
                                                    'class': 'd-table green-text'
                                                }).attr('target', '_blank')
                                            ]
                                        })
                                    ]
                                }).mark(obj.words, {
                                    'element': 'span',
                                    'className': 'marked yellow black-text',
                                    'accuracy': 'complementary'
                                });

                                if (o.deleted_at)
                                {
                                    model.css({ 'opacity': '.4' })
                                }
                            break;
                            case 'comment':
                                var model = $('<div />', {
                                    'html': [
                                        $('<div />', {
                                            'html': [
                                                $('<a />', {
                                                    'html': o.channel.title,
                                                    'href': 'https://www.youtube.com/channel/' + o.channel.id,
                                                    'class': 'd-table red-text'
                                                }).attr('target', '_blank'),
                                                $('<time>', {
                                                    'html': o.created_at,
                                                    'class': 'd-table grey-text text-lighten-1'
                                                }),
                                                $('<span />', {
                                                    'html': o.text,
                                                    'class': 'grey-text text-darken-2'
                                                }),
                                                $('<a />', {
                                                    'html': 'https://www.youtube.com/watch?v=' + o.video_id,
                                                    'href': 'https://www.youtube.com/watch?v=' + o.video_id,
                                                    'class': 'd-table green-text'
                                                }).attr('target', '_blank')
                                            ]
                                        })
                                    ]
                                }).mark(obj.words, {
                                    'element': 'span',
                                    'className': 'marked yellow black-text',
                                    'accuracy': 'complementary'
                                });

                                if (o.deleted_at)
                                {
                                    model.css({ 'opacity': '.4' })
                                }
                            break;
                            case 'video':
                                var model = $('<div />', {
                                    'html': [
                                        $('<div />', {
                                            'html': [
                                                $('<span />', {
                                                    'html': o.title,
                                                    'class': 'd-table blue-text title'
                                                }),
                                                $('<a />', {
                                                    'html': o.channel.title,
                                                    'href': 'https://www.youtube.com/channel/' + o.channel.id,
                                                    'class': 'd-table red-text'
                                                }).attr('target', '_blank'),
                                                $('<time>', {
                                                    'html': o.created_at,
                                                    'class': 'd-table grey-text text-lighten-1'
                                                }),
                                                $('<span />', {
                                                    'html': o.text,
                                                    'class': 'grey-text text-darken-2'
                                                }),
                                                $('<a />', {
                                                    'html': 'https://www.youtube.com/watch?v=' + o._id,
                                                    'href': 'https://www.youtube.com/watch?v=' + o._id,
                                                    'class': 'd-table green-text'
                                                }).attr('target', '_blank')
                                            ]
                                        })
                                    ]
                                }).mark(obj.words, {
                                    'element': 'span',
                                    'className': 'marked yellow black-text',
                                    'accuracy': 'complementary'
                                });

                                if (o.deleted_at)
                                {
                                    model.css({ 'opacity': '.4' })
                                }
                            break;
                        }

                        $('<div />', {
                            'class': 'mt-1',
                            'html': [
                                $('<a />', {
                                    'class': 'btn-floating btn-small waves-effect white',
                                    'href': '{{ url('/') }}/db/' + o._index + '/' + o._type + '/' + o._id,
                                    'html': $('<i />', {
                                        'class': 'material-icons grey-text text-darken-2',
                                        'html': 'info'
                                    })
                                }),
                                $('<span />', { 'html': ' ' }),
                                $('<a />', {
                                    'href': '#',
                                    'html': $('<i />', {
                                        'class': 'material-icons grey-text text-darken-2',
                                        'html': 'add'
                                    }),
                                    'class': 'btn-floating btn-small waves-effect white json',
                                    'data-href': '{{ route('pin', 'add') }}',
                                    'data-method': 'post',
                                    'data-include': 'group_id',
                                    'data-callback': '__pin',
                                    'data-trigger': 'pin',
                                    'data-id': o._id,
                                    'data-pin-uuid': o.uuid,
                                    'data-index': o._index,
                                    'data-type': o._type
                                })
                            ]
                        }).appendTo(model)

                        item.html(model)
                        item.appendTo(ul)
                })

                $('.dropdown-trigger').dropdown({
                    alignment: 'right'
                })
            }
        }
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
                'html': $('<canvas />', { 'id': __.data('type') + '-chart', 'height': '200' })
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
                    type: 'line',
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
                var data = [];
                var option = histogramOption;
                    option['title']['text'] = 'GÜNLÜK İÇERİK GRAFİĞİ';

                $.each(obj.data.results, function(key, o) {
                    data.push(o.doc_count);
                })

                new Chart(document.getElementById(__.data('type') + '-chart'), {
                    type: 'line',
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
        items: 1,
        singleItem: true
    })

    $('.owl-chips').owlCarousel({
        responsiveClass: true,
        autoWidth: true,
        dotClass: 'hide'
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
                type: 'line',
                data: data,
                options: options
            })
        }, 100)
    }

    $(document).on('click', '[data-search]', function() {
        var input = $('input[name=string]');
        var search = $('ul#search');

            input.val($(this).data('search'))

            search.data('skip', 0).addClass('json-clear')

        setTimeout(function() {
            vzAjax(search)
        }, 400)

        M.toast({ html: 'Arama güncelleniyor...' })
    }).on('keyup', 'input[name=string]', function(e) {
        var __ = $(this);
        var keycode = (e.keyCode ? e.keyCode : e.which);

        if (keycode == '13')
        {
            chip(__)
        }
    })

    function chip(__)
    {
        var id = hashCode(__.val());

        if (!$('.chip-s').find('.chip[data-id=' + id + ']').length)
        {
            $('.chip-s').prepend($('<a />', {
                'href': '#',
                'class': 'chip waves-effect white grey-text text-darken-2',
                'data-search': __.val(),
                'html': __.val(),
                'data-id': id
            }))
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
    <div class="grey-text mb-1" data-name="stats"></div>
    <div class="card hide">
        <div class="time-line">
            <ul class="collection json-clear" 
                id="search"
                data-href="{{ route('search.dashboard') }}"
                data-skip="0"
                data-take="100"
                data-more-button="#search-more_button"
                data-callback="__search_archive"
                data-method="post"
                data-include="start_date,end_date,sentiment,modules,string,sort"
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

    <div class="center-align">
        <button class="btn-flat waves-effect hide json"
                id="search-more_button"
                type="button"
                data-json-target="ul#search">Daha Fazla</button>
    </div>
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

@section('wildcard')
    <div class="container container-wide">
        <div class="wild-area">
            <div class="wild-content d-flex" data-wild="date">
                <span class="wild-body d-flex">
                    <a href="#" class="btn-floating btn-flat btn-small waves-effect align-self-center mr-1" data-class=".wild-content" data-class-remove="active">
                        <i class="material-icons">close</i>
                    </a>
                    <input style="max-width: 96px;" type="text" class="datepicker" name="start_date" value="{{ $s ? $s : date('d.m.Y', strtotime('-1 day')) }}" placeholder="Başlangıç" />
                    <input style="max-width: 96px;" type="text" class="datepicker" name="end_date" value="{{ $e ? $e : date('d.m.Y') }}" placeholder="Bitiş" />
                </span>
            </div>
            <div class="wild-content d-flex" data-wild="sentiment">
                <span class="wild-body d-flex">
                    <a href="#" class="btn-floating btn-flat btn-small waves-effect align-self-center mr-1" data-class=".wild-content" data-class-remove="active">
                        <i class="material-icons">close</i>
                    </a>
                    <label class="align-self-center mr-1" data-tooltip="Pozitif">
                        <input type="radio" name="sentiment" value="pos" />
                        <span class="material-icons grey-text text-darken-2">sentiment_satisfied</span>
                    </label>
                    <label class="align-self-center mr-1" data-tooltip="Nötr">
                        <input type="radio" name="sentiment" value="neu" />
                        <span class="material-icons grey-text text-darken-2">sentiment_neutral</span>
                    </label>
                    <label class="align-self-center mr-1" data-tooltip="Negatif">
                        <input type="radio" name="sentiment" value="neg" />
                        <span class="material-icons grey-text text-darken-2">sentiment_dissatisfied</span>
                    </label>
                    <label class="align-self-center mr-1" data-tooltip="Tümü">
                        <input type="radio" name="sentiment" value="all" checked="" />
                        <span class="material-icons grey-text text-darken-2">fullscreen</span>
                    </label>
                </span>
            </div>
            <div class="wild-content d-flex" data-wild="graph">
                <span class="wild-body d-flex">
                    <a href="#" class="btn-floating btn-flat btn-small waves-effect align-self-center mr-1" data-class=".wild-content" data-class-remove="active">
                        <i class="material-icons">close</i>
                    </a>
                    <button type="button" data-type="hourly" data-tooltip="Saatlik İçerik Grafiği" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string,modules" data-href="{{ route('search.aggregation') }}" data-method="post" class="btn-flat btn-small json waves-effect align-self-center" style="margin: 0 .2rem 0 0;">Saatlik</button>
                    <button type="button" data-type="daily" data-tooltip="Günlük İçerik Grafiği" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string,modules" data-href="{{ route('search.aggregation') }}" data-method="post" class="btn-flat btn-small json waves-effect align-self-center" style="margin: 0 .2rem 0 0;">Günlük</button>
                    <button type="button" data-type="location" data-tooltip="Konum Grafiği" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string,modules" data-href="{{ route('search.aggregation') }}" data-method="post" class="btn-flat btn-small json waves-effect align-self-center" style="margin: 0 .2rem 0 0;">Konum</button>
                    <button type="button" data-type="platform" data-tooltip="Platform Grafiği" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string,modules" data-href="{{ route('search.aggregation') }}" data-method="post" class="btn-flat btn-small json waves-effect align-self-center" style="margin: 0 .2rem 0 0;">Platform</button>
                    <button type="button" data-type="source" data-tooltip="Kaynak Grafiği" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string,modules" data-href="{{ route('search.aggregation') }}" data-method="post" class="btn-flat btn-small json waves-effect align-self-center" style="margin: 0 .2rem 0 0;">Kaynak</button>
                    <button type="button" data-type="mention" data-tooltip="Kimler Bahsetti?" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string,modules" data-href="{{ route('search.aggregation') }}" data-method="post" class="btn-flat btn-small json waves-effect align-self-center" style="margin: 0 .2rem 0 0;">@</button>
                    <button type="button" data-type="hashtag" data-tooltip="Hangi Hashtagler Kullanıldı?" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string,modules" data-href="{{ route('search.aggregation') }}" data-method="post" class="btn-flat btn-small json waves-effect align-self-center" style="margin: 0 .2rem 0 0;">#</button>
                    <button type="button" data-type="sentiment" data-tooltip="Duygu Grafiği" data-callback="__aggregation" data-include="start_date,end_date,sentiment,string,modules" data-href="{{ route('search.aggregation') }}" data-method="post" class="btn-flat btn-small json waves-effect align-self-center" style="margin: 0 .2rem 0 0;">
                        <i class="material-icons">sentiment_satisfied</i>
                    </button>
                </span>
            </div>
            <div class="wild-content d-flex" data-wild="settings">
                <span class="wild-body d-flex">
                    <a href="#" class="btn-floating btn-flat btn-small waves-effect align-self-center mr-1" data-class=".wild-content" data-class-remove="active">
                        <i class="material-icons">close</i>
                    </a>
                    <label class="align-self-center mr-1">
                        <input type="checkbox" name="sort" value="asc" />
                        <span class="grey-text text-darken-2">İlk İçerikler</span>
                    </label>
                </span>
            </div>
            <ul class="wild-menu">
                <li>
                    <a class="d-flex" href="#" data-class="[data-wild=date]" data-class-add="active">
                        <i class="material-icons">date_range</i>
                    </a>
                </li>
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
                    <a class="d-flex" href="#" data-class="[data-wild=settings]" data-class-add="active">
                        <i class="material-icons">settings</i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="z-depth-1">
        <div class="search-field grey lighten-4">
            <div class="container container-wide">
                <div class="d-flex">
                    <input
                        class="validate json json-search white align-self-center"
                        id="string"
                        name="string"
                        type="search"
                        data-json-target="ul#search"
                        placeholder="Ara"
                        value="{{ $q }}"
                        style="margin: 0 .4rem 0 0;" />
                    <button
                        class="btn-flat waves-effect align-self-center json"
                        type="button"
                        data-json-target="ul#search"
                        data-enter="input[name=string]"
                        data-clear="true"
                        style="margin: 0 .4rem 0 0;">
                        <i class="material-icons">search</i>
                    </button>
                    <a
                        class="btn-flat waves-effect align-self-center"
                        href="#"
                        data-trigger="info"
                        style="margin: 0 .4rem 0 0;">
                        <i class="material-icons text-darken-2">info_outline</i>
                    </a>
                </div>
                <div class="chip-s owl-chips owl-carousel pt-1">
                    @if (@$trends)
                        @foreach ($trends as $trend)
                            <a class="chip teal white-text waves-effect" data-search="{{ $trend->title }}" href="#">{{ $trend->title }}</a>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="z-depth-1 hide" id="chart-area">
        <div class="container container-wide">
            <div class="pt-1 pb-1">
                <div class="owl-carousel owl-wildcard"></div>
            </div>
        </div>
    </div>
@endsection

@section('dock')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Kaynak</span>
        </div>
        <div class="collection collection-bordered">
            @foreach (config('system.modules') as $key => $module)
                <label class="collection-item waves-effect d-block">
                    <input name="modules" checked value="{{ $key }}" data-multiple="true" type="checkbox" />
                    <span>{{ $module }}</span>
                </label>
            @endforeach
        </div>
    </div>

    @include('pin.group.dock')
@endsection
