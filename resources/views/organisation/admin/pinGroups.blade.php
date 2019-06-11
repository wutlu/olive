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
            'text' => '🐞 Pin Grupları'
        ]
    ],
    'dock' => true,
    'footer_hide' => true
])

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Pin Grupları</span>
        </div>
        <ul id="pin-groups"
             class="collection collection-unstyled load json-clear" 
             data-href="{{ route('admin.organisation.pin_groups', $organisation->id) }}"
             data-skip="0"
             data-take="5"
             data-more-button="#pin-groups-more_button"
             data-callback="__pin_groups"
             data-method="post"
             data-loader="#home-loader"
             data-nothing>
            <li class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </li>
            <li data-name="item" class="collection-item model hide">
                <span>
                    <span data-name="name" class="d-table"></span>
                    <time data-name="created-at" class="timeago grey-text"></time>
                </span>
                <span data-name="count" class="grey-text ml-auto"></span>
            </li>
        </ul>
        @component('components.loader')
            @slot('color', 'blue-grey')
            @slot('id', 'home-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>

    <div class="center-align">
        <button class="btn-flat waves-effect hide json"
                id="pin-groups-more_button"
                type="button"
                data-json-target="#pin-groups">Öncekiler</button>
    </div>
@endsection

@section('dock')
    @include('organisation.admin._menu', [ 'active' => 'groups.pin', 'id' => $organisation->id ])
@endsection

@push('local.scripts')
    function __pin_groups(__, obj)
    {
        var ul = $('#pin-groups');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp d-flex').attr('data-id', o.id)

                        item.find('[data-name=name]').html(o.name)
                        item.find('[data-name=created-at]').attr('data-time', o.created_at).html(o.created_at)
                        item.find('[data-name=count]').html(o.pins_count + ' pin')

                        item.appendTo(ul)
                })
            }
        }
    }
@endpush
