@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Alarmlar'
        ]
    ]
])

@section('action-bar')
    <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-trigger="create-alarm">
        <i class="material-icons grey-text text-darken-2">add</i>
    </a>
@endsection

@push('local.scripts')
    function __collections(__, obj)
    {
        var ul = $('#alarms');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var selector = $('[data-id=' + o.id + '].card'),

                        item = selector.length ? selector : item_model.clone();

                        item.removeClass('model hide')
                            .addClass('_tmp')
                            .attr('data-id', o.id)

                        item.find('[data-name=name]').html(o.name)
                        item.find('[data-name=query]').html(o.query)
                        item.find('[data-name=hit]').html(o.hit).addClass(o.hit == 0 ? 'red-text' : '')
                        item.find('[data-name=interval]').html(o.interval)

                        $.each({
                            'day_1': 'day-1',
                            'day_2': 'day-2',
                            'day_3': 'day-3',
                            'day_4': 'day-4',
                            'day_5': 'day-5',
                            'day_6': 'day-6',
                            'day_7': 'day-7',
                        }, function(key, name) {
                            item.find('[data-name=' + name + ']').removeClass('grey teal').addClass(o.weekdays.includes(key) ? 'teal' : 'grey')
                        })

                        item.find('[data-name=start-time]').html(o.start_time)
                        item.find('[data-name=end-time]').html(o.end_time)

                        item.find('[data-name=dropdown-content]').attr('id', 'dropdown-' + o.id)
                        item.find('[data-name=dropdown-trigger]').attr('data-target', 'dropdown-' + o.id).addClass('dropdown-trigger')

                        item.find('[data-name=edit]').attr('data-id', o.id)
                        item.find('[data-trigger=delete]').attr('data-id', o.id)

                        if (!selector.length)
                        {
                            ul.prepend(item)
                        }
                })

                $('.dropdown-trigger').dropdown({
                    alignment: 'right'
                })
            }

            $('[data-name=count]').html(obj.hits.length + '/{{ auth()->user()->organisation->capacity*2 }}')
        }
    }
@endpush

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Alarmlar</span>
            <span class="d-block" data-name="count"></span>
            <p class="grey-text text-darken-2">İlgilendiğiniz konularda alarm oluşturarak gündemden çok daha hızlı bir şekidle haberdar olabilirsiniz.</p>
        </div>

        @component('components.loader')
            @slot('color', 'cyan')
            @slot('class', 'card-loader-unstyled')
            @slot('id', 'home-loader')
        @endcomponent
    </div>

    <div class="card-group load"
         id="alarms"
         data-href="{{ route('alarm.data') }}"
         data-callback="__collections"
         data-method="post"
         data-loader="#home-loader"
         data-nothing>
        <div class="nothing hide">
            @component('components.nothing')
                @slot('cloud_class', 'white-text')
                @slot('text_class', 'grey-text text-darken-2')
                @slot('size', 'small')
                @slot('text', '+ butonunu kullanarak yeni bir alarm oluşturabilirsiniz.')
            @endcomponent
        </div>
        <div class="card card-alarm hoverable model hide">
            <div class="group d-flex">
                <div class="card-content grey lighten-5">
                    <small class="grey-text">Kalan Bildirim</small>
                    <span class="d-block" data-name="hit"></span>
                    <small class="grey-text">Bildirim Aralığı</small>
                    <span class="d-block">
                        <span data-name="interval"></span> dakika
                    </span>
                </div>
                <div class="card-content">
                    <span class="card-title card-title-small" data-name="name"></span>
                    <small class="grey-text">Sorgu</small>
                    <span class="d-block" data-name="query"></span>

                    <a href="#" data-name="dropdown-trigger" class="btn-floating btn-flat btn-small waves-effect">
                        <i class="material-icons">arrow_drop_down</i>
                    </a>
                    <ul class="dropdown-content" data-name="dropdown-content">
                        <li>
                            <a
                                href="#"
                                data-name="edit"
                                data-method="post"
                                data-href="{{ route('alarm') }}"
                                data-callback="__get_alarm"
                                class="json">Güncelle</a>
                        </li>
                        <li>
                            <a href="#" data-trigger="delete">Sil</a>
                        </li>
                    </ul>
                </div>
            </div>

            <ul class="days d-flex">
                <li class="day lighten-2 white-text" data-name="day-1">Pt</li>
                <li class="day lighten-2 white-text" data-name="day-2">Sa</li>
                <li class="day lighten-2 white-text" data-name="day-3">Ça</li>
                <li class="day lighten-2 white-text" data-name="day-4">Pe</li>
                <li class="day lighten-2 white-text" data-name="day-5">Cu</li>
                <li class="day lighten-3 white-text" data-name="day-6">Ct</li>
                <li class="day lighten-3 white-text" data-name="day-7">Pa</li>
                <li class="hour grey lighten-2 grey-text" data-name="start-time"></li>
                <li class="hour grey lighten-2 grey-text" data-name="end-time"></li>
            </ul>
        </div>
    </div>
@endsection

@push('local.styles')
    .card-alarm > ul.days {
        margin: 0;
    }
    .card-alarm > ul.days > li {
        height: 24px;
        line-height: 24px;

        display: table-row;

        -ms-flex: 1 1 auto;
            flex: 1 1 auto;

        text-align: center;
    }
    .card-alarm > ul.days > li.day {
        width: 24px;
    }
    .card-alarm > .group > .card-content:first-child {
        width: 128px;
    }
    .card-alarm > .group > .card-content:last-child {
        width: calc(100% - 96px);
    }
    .card-alarm > .group > .card-content > .dropdown-trigger {
        position: absolute;
        right: 1rem;
        top: 1rem;
    }
@endpush

@push('local.scripts')
    function alarm_modal()
    {
        var mdl = modal({
            'id': 'alarm',
            'body': $('<form />', {
                'action': '{{ route('alarm') }}',
                'id': 'alarm-form',
                'class': 'json',
                'date-method': 'post',
                'data-callback': '__alarm_callback',
                'html': [
                    $('<ul />', {
                        'class': 'collection mb-0',
                        'html': [
                            $('<li />', {
                                'class': 'collection-item',
                                'html': $('<div />', {
                                    'class': 'input-field m-0',
                                    'html': [
                                        $('<input />', {
                                            'name': 'name',
                                            'id': 'name',
                                            'type': 'text',
                                            'class': 'validate',
                                            'data-length': 100
                                        }),
                                        $('<label />', {
                                            'for': 'name',
                                            'html': 'Alarm Adı'
                                        }),
                                        $('<span />', {
                                            'class': 'helper-text',
                                            'html': 'Oluşturacağınız alarmın adını girin.'
                                        })
                                    ]
                                })
                            }),
                            $('<li />', {
                                'class': 'collection-item',
                                'html': $('<div />', {
                                    'class': 'input-field m-0',
                                    'html': [
                                        $('<input />', {
                                            'name': 'text',
                                            'id': 'text',
                                            'type': 'text',
                                            'class': 'validate',
                                            'data-length': 255
                                        }),
                                        $('<label />', {
                                            'for': 'text',
                                            'html': 'Sorgu'
                                        }),
                                        $('<a />', {
                                            'href': '#',
                                            'class': 'd-flex',
                                            'data-trigger': 'info',
                                            'html': [
                                                $('<i />', {
                                                    'class': 'material-icons mr-1 grey-text align-self-center',
                                                    'html': 'info_outline'
                                                }),
                                                $('<span />', {
                                                    'class': 'grey-text align-self-center',
                                                    'html': 'Arama İfadeleri'
                                                })
                                            ]
                                        }),
                                        $('<span />', {
                                            'class': 'helper-text',
                                            'html': 'Hassas kelimeler ve dakikalık bildirimler durumunda rahatsız edici bildirim e-postaları alabilirsiniz. Böyle bir durumla karşılaşmamak için, ilgi odaklı çalışmanız gerekmektedir.'
                                        })
                                    ]
                                })
                            }),
                            $('<li />', {
                                'class': 'collection-item',
                                'html': [
                                    $('<span />', {
                                        'class': 'grey-text text-darken-2',
                                        'html': 'Çalışılacak Saat Aralığı'
                                    }),
                                    $('<div />', {
                                        'class': 'd-flex',
                                        'html': [
                                            $('<div />', {
                                                'class': 'input-field m-0',
                                                'css': { 'width': '72px' },
                                                'html': $('<input />', {
                                                    'name': 'start_time',
                                                    'id': 'start_time',
                                                    'type': 'text',
                                                    'value': '09:00',
                                                    'class': 'validate timepicker'
                                                })
                                            }),
                                            $('<div />', {
                                                'class': 'input-field m-0',
                                                'css': { 'width': '72px' },
                                                'html': $('<input />', {
                                                    'name': 'end_time',
                                                    'id': 'end_time',
                                                    'type': 'text',
                                                    'value': '18:00',
                                                    'class': 'validate timepicker'
                                                })
                                            }),
                                            $('<div />', {
                                                'class': 'input-field m-0',
                                                'css': { 'width': '64px' },
                                                'html': $('<input />', {
                                                    'name': 'interval',
                                                    'id': 'interval',
                                                    'type': 'number',
                                                    'value': '5',
                                                    'min': '1',
                                                    'max': '120',
                                                    'class': 'validate'
                                                })
                                            }),
                                            $('<div />', {
                                                'class': 'input-field m-0',
                                                'css': { 'width': '64px' },
                                                'html': $('<input />', {
                                                    'name': 'hit',
                                                    'id': 'hit',
                                                    'type': 'number',
                                                    'value': '20',
                                                    'min': '1',
                                                    'max': '120',
                                                    'class': 'validate'
                                                })
                                            })
                                        ]
                                    }),
                                    $('<span />', {
                                        'class': 'grey-text',
                                        'html': 'Örnek kullanım: "<span class="teal-text">09:00</span> ile <span class="teal-text">18:00</span> arası <span class="teal-text">5</span> dakikada bir toplamda <span class="teal-text">20</span> adet bildirim gönder."'
                                    })
                                ]
                            }),
                            $('<li />', {
                                'class': 'collection-item',
                                'html': $('<div />', {
                                    'class': 'd-flex flex-wrap',
                                    'html': [
                                        $('<div />', {
                                            'css': { 'min-width': '50%' },
                                            'html': [
                                                $('<h6 />', {
                                                    'html': 'Çalışılacak Günler'
                                                }),
                                                @foreach ([
                                                    'day_1' => 'Pazartesi',
                                                    'day_2' => 'Salı',
                                                    'day_3' => 'Çarşamba',
                                                    'day_4' => 'Perşembe',
                                                    'day_5' => 'Cuma',
                                                    'day_6' => 'Cumartesi',
                                                    'day_7' => 'Pazar'
                                                ] as $key => $day)
                                                    $('<label />', {
                                                        'class': 'd-block',
                                                        'html': [
                                                            $('<input />', {
                                                                'type': 'checkbox',
                                                                'name': 'weekdays',
                                                                'data-multiple': 'true',
                                                                'value': '{{ $key }}'
                                                            }),
                                                            $('<span />', {
                                                                'html': '{{ $day }}'
                                                            })
                                                        ]
                                                    }),
                                                @endforeach
                                            ]
                                        }),
                                        $('<div />', {
                                            'css': { 'min-width': '50%' },
                                            'html': [
                                                $('<h6 />', {
                                                    'html': 'Kaynaklar'
                                                }),
                                                @foreach (config('system.modules') as $key => $module)
                                                    $('<label />', {
                                                        'class': 'd-block',
                                                        'html': [
                                                            $('<input />', {
                                                                'type': 'checkbox',
                                                                'name': 'sources',
                                                                'data-multiple': 'true',
                                                                'value': '{{ $key }}'
                                                            }),
                                                            $('<span />', {
                                                                'html': '{{ $module }}'
                                                            })
                                                        ]
                                                    }),
                                                @endforeach
                                            ]
                                        })
                                    ]
                                })
                            }),
                            $('<li />', {
                                'class': 'collection-item',
                                'html': [
                                    $('<h6 />', {
                                        'html': 'Bildirim Gönderilecek Kullanıcılar'
                                    }),
                                    @foreach ($members as $member)
                                        $('<label />', {
                                            'class': 'd-block',
                                            'css': { 'width': '100%' },
                                            'html': [
                                                $('<input />', {
                                                    'type': 'checkbox',
                                                    'name': 'user_ids',
                                                    'data-multiple': 'true',
                                                    'value': '{{ $member->id }}'
                                                }),
                                                $('<span />', {
                                                    'html': '{{ $member->email }}'
                                                })
                                            ]
                                        }),
                                    @endforeach
                                ]
                            })
                        ]
                    })
                ]
            }),
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect btn-flat grey-text',
                    'html': buttons.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<button />', {
                    'type': 'submit',
                    'class': 'waves-effect btn-flat',
                    'data-submit': 'form#alarm-form',
                    'html': buttons.ok
                })
            ],
            'size': 'modal-large',
            'options': {
                dismissible: false
            }
        })

            mdl.find('[data-length]').characterCounter()

        M.updateTextFields()

        $('.timepicker').timepicker({
            format: 'hh:MM',
            twelveHour: false,
            i18n: date.i18n,
            container: 'body'
        })

        return mdl;
    }

    $(document).on('click', '[data-trigger=create-alarm]', function() {
        var mdl = alarm_modal();
            mdl.find('.modal-title').html('Alarm Oluştur')
            mdl.find('form#alarm-form').data('method', 'put')
            mdl.modal('open')
    })

    function __get_alarm(__, obj)
    {
        if (obj.status == 'ok')
        {
            var mdl = alarm_modal();
                mdl.find('.modal-title').html('Alarm Güncelle')
                mdl.find('form#alarm-form').data('id', obj.data.id).data('method', 'patch')

                mdl.find('[name=name]').val(obj.data.name)
                mdl.find('[name=text]').val(obj.data.query)
                mdl.find('[name=start_time]').val(obj.data.start_time)
                mdl.find('[name=end_time]').val(obj.data.end_time)
                mdl.find('[name=interval]').val(obj.data.interval)
                mdl.find('[name=hit]').val(obj.data.hit)

            $.each(obj.data.weekdays, function(key, day) {
                mdl.find('[name=weekdays][value=' + day + ']').prop('checked', true)
            })

            $.each(obj.data.modules, function(key, source) {
                mdl.find('[name=sources][value=' + source + ']').prop('checked', true)
            })

            $.each(obj.data.user_ids, function(key, id) {
                mdl.find('[name=user_ids][value=' + id + ']').prop('checked', true)
            })

                mdl.modal('open')
        }
    }

    function __alarm_callback(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-alarm').modal('close')

            vzAjax($('#alarms'))

            M.toast({
                html: obj.type == 'created' ? 'Alarm Oluşturuldu' : 'Alarm Güncellendi',
                classes: 'green darken-2'
            })
        }
    }

    $(document).on('click', '[data-trigger=delete]', function() {
        var mdl = modal({
            'id': 'alarm-alert',
            'body': 'Alarm silinecek?',
            'size': 'modal-small',
            'title': 'Sil',
            'options': {},
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect btn-flat grey-text',
                    'html': buttons.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<a />', {
                    'href': '#',
                    'class': 'waves-effect btn-flat red-text json',
                    'html': buttons.ok,
                    'data-href': '{{ route('alarm') }}',
                    'data-method': 'delete',
                    'data-id': $(this).data('id'),
                    'data-callback': '__delete_alarm'
                })
            ]
        })
    })

    function __delete_alarm(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#alarms').children('[data-id=' + obj.data.id + ']').remove()

            $('#modal-alarm-alert').modal('close')

            setTimeout(function() {
                $('#modal-alarm').modal('close')
            }, 200)

            M.toast({
                html: 'Alarm Silindi',
                classes: 'red darken-2'
            })

            vzAjax($('#alarms'))
        }
    }
@endpush

@include('_inc.alerts.search_operators')
