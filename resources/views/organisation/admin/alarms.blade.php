@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Organizasyonlar',
            'link' => route('admin.organisation.list')
        ],
        [
            'text' => $organisation->name,
            'link' => route('admin.organisation', $organisation->id)
        ],
        [
            'text' => '🐞 Alarmlar'
        ]
    ],
    'dock' => true,
    'footer_hide' => true
])

@section('content')
    <div class="card with-bg mb-1">
        <div class="card-content">
            <span class="card-title">Alarmlar</span>
            <span class="d-block grey-text text-darken-2" data-name="count">0 / 0</span>
        </div>

        <div class="card-group load"
             id="alarms"
             data-href="{{ route('admin.organisation.alarms', $organisation->id) }}"
             data-callback="__collections"
             data-method="post"
             data-loader="#home-loader"
             data-nothing>
            <div class="nothing hide pb-1">
                @component('components.nothing')
                    @slot('text_class', 'grey-text text-darken-2')
                    @slot('size', 'small')
                @endcomponent
            </div>
            <div data-name="item" class="card card-unstyled card-alarm hoverable model hide">
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

        @component('components.loader')
            @slot('color', 'blue-grey')
            @slot('id', 'home-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>
@endsection

@section('dock')
    @include('organisation.admin._menu', [ 'active' => 'alarms', 'id' => $organisation->id ])
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

                        if (!selector.length)
                        {
                            ul.prepend(item)
                        }
                })
            }
        }
    }
@endpush
