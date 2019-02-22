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
        var ul = $('#alarm');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var selector = $('[data-id=' + o.id + '].collection-item'),

                        item = selector.length ? selector : item_model.clone();

                        item.removeClass('model hide')
                            .addClass('_tmp')
                            .attr('data-id', o.id)

                        if (!selector.length)
                        {
                            item.appendTo(ul)
                        }
                })

                $('[data-tooltip]').tooltip()
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
                    <small class="grey-text">Alıcılar</small>
                    <span class="d-block" data-name="emails"></span>
                    <small class="grey-text">Kaynaklar</small>
                    <span class="d-block" data-name="sources"></span>
                </div>
            </div>

            <ul class="days d-flex">
                <li class="day red lighten-2 white-text" data-name="day-1">Pt</li>
                <li class="day red lighten-2 white-text" data-name="day-2">Sa</li>
                <li class="day red lighten-2 white-text" data-name="day-3">Ça</li>
                <li class="day red lighten-2 white-text" data-name="day-4">Pe</li>
                <li class="day red lighten-2 white-text" data-name="day-5">Cu</li>
                <li class="day red lighten-3 white-text" data-name="day-6">Ct</li>
                <li class="day red lighten-3 white-text" data-name="day-7">Pa</li>
                <li class="hour grey lighten-2 grey-text" data-name="start-time"></li>
                <li class="hour grey lighten-2 grey-text" data-name="end-time"></li>
            </ul>
        </div>
    </div>

    <div id="modal-alarm" class="modal with-bg modal-large">
        <div class="modal-content">
            <div class="modal-title"></div>
            <div class="modal-body">
                <form>
                    <ul class="collection mb-0">
                        <li class="collection-item">
                            <div class="input-field" style="margin: 0;">
                                <input name="name" id="name" type="text" class="validate" data-length="100" />
                                <label for="name">Alarm Adı</label>
                                <span class="helper-text">Oluşturacağınız alarmın adını girin.</span>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div class="input-field" style="margin: 0;">
                                <textarea id="query" name="query" class="materialize-textarea"></textarea>
                                <label for="query">Sorgu</label>
                                <a href="#" class="d-flex" data-trigger="info">
                                    <i class="material-icons mr-1 grey-text align-self-center">info_outline</i>
                                    <span class="grey-text align-self-center">Arama İfadeleri</span>
                                </a>
                                <span class="helper-text"></span>
                            </div>
                        </li>
                        <li class="collection-item">
                            <span class="grey-text text-darken-2">Çalışacak Saat Aralığı</span>
                            <div class="d-flex">
                                <div class="input-field m-0" style="width: 72px;">
                                    <input name="start_time" id="start_time" type="text" value="09:00" class="validate timepicker" />
                                </div>
                                <div class="input-field m-0" style="width: 72px;">
                                    <input name="end_time" id="end_time" type="text" value="18:00" class="validate timepicker" />
                                </div>
                                <div class="input-field m-0" style="width: 64px;">
                                    <input name="interval" id="interval" type="number" value="5" min="1" max="120" class="validate" />
                                </div>
                            </div>
                            <span class="grey-text">
                                Örnek kullanım: "<span class="teal-text">09:00</span> ile <span class="teal-text">18:00</span> arası <span class="teal-text">5</span> dakikada bir bildirim gönder."
                            </span>
                        </li>
                        <li class="collection-item">
                            <div class="d-flex flex-wrap">
                                <div style="min-width: 50%;">
                                    <h6>Çalışacak Günler</h6>
                                    @foreach ([
                                        'day_1' => 'Pazartesi',
                                        'day_2' => 'Salı',
                                        'day_3' => 'Çarşamba',
                                        'day_4' => 'Perşembe',
                                        'day_5' => 'Cuma',
                                        'day_6' => 'Cumartesi',
                                        'day_7' => 'Pazar'
                                    ] as $key => $day)
                                        <label class="d-block">
                                            <input type="checkbox" name="{{ $key }}" />
                                            <span>{{ $day }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <div style="min-width: 50%;">
                                    <h6>Kaynaklar</h6>
                                    @foreach (config('system.modules') as $key => $module)
                                        <label class="d-block">
                                            <input type="checkbox" name="{{ $key }}" />
                                            <span>{{ $module }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </li>
                        <li class="collection-item">
                            <div class="input-field" style="margin: 0;">
                                <textarea id="emails" name="emails" class="materialize-textarea"></textarea>
                                <label for="emails">Alıcı E-posta Adresleri</label>
                                <span class="helper-text"></span>
                            </div>
                        </li>
                    </ul>
                </form>
            </div>
        </div>
        <div class="modal-footer">
            <a data-trigger="delete-alarm" href="#" class="waves-effect btn-flat red-text hide">Sil</a>
            <button type="submit" class="waves-effect btn-flat" data-submit="form#test">Tamam</button>
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
@endpush

@push('local.scripts')
    $('.timepicker').timepicker({
        format: 'hh:MM',
        twelveHour: false,
        i18n: date.i18n
    })

    $('[data-length]').characterCounter()

    $(document).on('click', '[data-trigger=create-alarm]', function() {
        var mdl = $('#modal-alarm');
            mdl.find('.modal-title').html('Alarm Oluştur')
            mdl.find('form#alarm-form').data('method', 'put')

            mdl.find('[name=name]').val('')

            mdl.modal('open')

        $('[data-trigger=delete-alarm]').removeAttr('data-id').addClass('hide')
    })

    function __get_alarm(__, obj)
    {
        if (obj.status == 'ok')
        {
            var mdl = $('#modal-alarm');
                mdl.find('.modal-title').html('Alarm Güncelle')
                mdl.find('form#alarm-form').data('id', obj.data.id).data('method', 'patch')
                mdl.find('[name=name]').val(obj.data.name)

                mdl.modal('open')

            $('[data-trigger=delete-alarm]').data('id', obj.data.id).removeClass('hide')
        }
    }

    function __alarm_callback(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-alarm').modal('close')

            if (obj.type == 'created')
            {
                vzAjax($('#alarms').data('skip', 0).addClass('json-clear'))
            }
            else if (obj.type == 'updated')
            {
                $('#alarms').children('[data-id=' + obj.data.id + ']').find('[data-trigger=pin-go]').html(obj.data.name)
            }

            M.toast({
                html: obj.type == 'created' ? 'Alarm Oluşturuldu' : obj.type == 'updated' ? 'Alarm Güncellendi' : 'İşlem Gerçekleşti',
                classes: 'green darken-2'
            })
        }
    }

    $(document).on('click', '[data-trigger=delete-alarm]', function() {
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

            vzAjax($('#alarms').data('skip', 0).addClass('json-clear'))
        }
    }
@endpush

@include('_inc.alerts.search_operators')
