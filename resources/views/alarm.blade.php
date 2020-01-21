@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Alarmlar'
        ]
    ],
    'footer_hide' => true
])

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

                        item.find('[data-name=hit]').html(o.hit).addClass(o.hit == 0 ? 'red-text' : '')
                        item.find('[data-name=interval]').html(o.interval)
                        item.find('[data-name=name]').html(o.search.name)
                        item.find('[data-name=string]').html(o.search.string)
                        item.find('[data-name=receivers]').html(o.user_ids.length + ' alıcı')

                        $.each({
                            'day_1': 'day-1',
                            'day_2': 'day-2',
                            'day_3': 'day-3',
                            'day_4': 'day-4',
                            'day_5': 'day-5',
                            'day_6': 'day-6',
                            'day_7': 'day-7',
                        }, function(key, name) {
                            item.find('[data-name=' + name + ']').removeClass('grey blue-grey').addClass(o.weekdays.includes(key) ? 'blue-grey' : 'grey')
                        })

                        item.find('[data-name=start-time]').html(o.start_time)
                        item.find('[data-name=end-time]').html(o.end_time)

                        item.find('[data-name=dropdown-trigger]').attr('data-target', 'dropdown-' + o.id).addClass('dropdown-trigger dropdown-trigger--content')
                        item.find('[data-name=dropdown-content]').attr('id', 'dropdown-' + o.id)

                        item.find('[data-name=edit]').attr('data-id', o.id)
                        item.find('[data-trigger=delete]').attr('data-id', o.id)

                        if (!selector.length)
                        {
                            ul.prepend(item)
                        }
                })

                $('.dropdown-trigger--content').dropdown({
                    alignment: 'right'
                })
            }

            $('[data-name=count]').html(obj.hits.length)
        }
    }
@endpush

@section('content')
    <div class="card with-bg">
        <div class="card-image">
            <img src="{{ asset('img/md-s/21.jpg') }}" alt="Image" />
            <span class="card-title white-text d-flex">
                <i class="material-icons align-self-center mr-1">access_alarm</i>
                Alarmlar
            </span>
            <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-trigger="create-alarm">
                <i class="material-icons grey-text text-darken-2">add</i>
            </a>
        </div>

        <div class="card-content">
            <span class="d-block grey-text text-darken-2" data-name="count">0</span>
        </div>

        @component('components.loader')
            @slot('color', 'blue-grey')
            @slot('id', 'home-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent

        <div class="card-group load"
             id="alarms"
             data-href="{{ route('alarm.data') }}"
             data-callback="__collections"
             data-method="post"
             data-loader="#home-loader"
             data-nothing>
            <div class="nothing hide pb-1">
                @component('components.nothing')
                    @slot('text_class', 'grey-text text-darken-2')
                    @slot('size', 'small')
                    @slot('text', '+ butonunu kullanarak yeni bir alarm oluşturabilirsiniz.')
                @endcomponent
            </div>
            <div class="card card-unstyled card-alarm hoverable model hide">
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
                        <small class="blue-grey-text">Sorgu</small>
                        <span class="d-block mb-1" data-name="string"></span>
                        <span class="blue-grey-text d-block" data-name="receivers"></span>

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
    </div>
@endsection

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
                        'class': 'collection collection-unstyled',
                        'html': [
                            $('<li />', {
                                'class': 'collection-item',
                                'html': [
                                    @if (count($searches))
                                        $('<div />', {
                                            'class': 'input-field',
                                            'html': [
                                                $('<select />', {
                                                    'name': 'search_id',
                                                    'id': 'search_id',
                                                    'html': [
                                                        $('<option />', {
                                                            'value': '',
                                                            'disabled': true,
                                                            'selected': true,
                                                            'html': 'Seçin'
                                                        }),
                                                        @foreach($searches as $search)
                                                            $('<option />', {
                                                                'value': {{ $search->id }},
                                                                'html': '{{ $search->name }}'
                                                            }),
                                                        @endforeach
                                                    ]
                                                }),
                                                $('<label />', {
                                                    'html': 'Kayıtlı Aramalar'
                                                })
                                            ]
                                        })
                                    @else
                                        $('<div />', {
                                            'class': 'teal-text',
                                            'html': 'Kayıtlı Arama bulunmuyor. Lütfen ilk önce <a href="{{ route('search.dashboard') }}" class="teal-text text-darken-4">Arama Motoru</a>\'nu kullanarak bir arama kaydedin.'
                                        })
                                    @endif
                                ]
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
                                                    'max': '1440',
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
                                        'html': 'Örnek kullanım: "<span class="blue-grey-text">09:00</span> ile <span class="blue-grey-text">18:00</span> arası <span class="blue-grey-text">5</span> dakikada bir toplamda <span class="blue-grey-text">20</span> adet bildirim gönder."'
                                    })
                                ]
                            }),
                            $('<li />', {
                                'class': 'collection-item',
                                'html': [
                                    $('<label />', {
                                        'class': 'd-block',
                                        'html': [
                                            $('<input />', {
                                                'type': 'checkbox',
                                                'name': 'report',
                                                'value': 'on'
                                            }),
                                            $('<span />', {
                                                'html': 'Detaylı Rapor'
                                            })
                                        ]
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
                            })
                        ]
                    })
                ]
            }),
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect btn-flat grey-text',
                    'html': keywords.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<button />', {
                    'type': 'submit',
                    'class': 'waves-effect btn-flat',
                    'data-submit': 'form#alarm-form',
                    'html': keywords.ok
                })
            ],
            'size': 'modal-large',
            'options': {
                dismissible: false
            }
        })

            mdl.find('[data-length]').characterCounter()
            mdl.find('select').formSelect()

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

                mdl.find('[name=start_time]').val(obj.data.start_time)
                mdl.find('[name=end_time]').val(obj.data.end_time)
                mdl.find('[name=interval]').val(obj.data.interval)
                mdl.find('[name=hit]').val(obj.data.hit)
                mdl.find('[name=search_id]').val(obj.data.search_id).formSelect()

            $.each(obj.data.weekdays, function(key, day) {
                mdl.find('[name=weekdays][value=' + day + ']').prop('checked', true)
            })

            $.each(obj.data.user_ids, function(key, id) {
                mdl.find('[name=user_ids][value=' + id + ']').prop('checked', true)
            })

            if (obj.data.report)
            {
                mdl.find('input[name=report]').prop('checked', true)
            }

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
                    'html': keywords.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<a />', {
                    'href': '#',
                    'class': 'waves-effect btn-flat red-text json',
                    'html': keywords.ok,
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
