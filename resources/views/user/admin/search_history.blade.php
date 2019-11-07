@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Kullanıcılar',
            'link' => route('admin.user.list')
        ],
        [
            'text' => $user->name,
            'link' => route('admin.user', $user->id)
        ],
        [
            'text' => '🐞 Arama Geçmişi'
        ]
    ],
    'dock' => true,
    'footer_hide' => true
])

@push('local.scripts')
    function __history(__, obj)
    {
        var ul = $('#history');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            item_model.addClass('hide')

            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide').addClass('_tmp').attr('data-id', o.id)

                        item.find('[data-name=query]').html(o.query)
                        item.find('[data-name=created-at]').attr('data-time', o.created_at).html(o.created_at)
                        item.find('[data-name=deleted-at]').attr('data-time', o.deleted_at).html(o.deleted_at)

                        item.appendTo(ul)
                })
            }
        }
    }
@endpush

@section('content')
    <div class="card mb-1">
        <div class="card-content">
            <span class="card-title d-flex">
                <i class="material-icons align-self-center mr-1">speaker_notes</i>
                Arama Geçmişi
            </span>
        </div>
        <nav class="nav-half mb-0">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#history"
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                </div>
            </div>
        </nav>
        <ul id="history"
             class="collection collection-hoverable load json-clear" 
             data-href="{{ route('admin.user.search_history', $user->id) }}"
             data-skip="0"
             data-take="25"
             data-include="string"
             data-more-button="#history-more_button"
             data-callback="__history"
             data-method="post"
             data-loader="#home-loader"
             data-nothing>
            <li class="collection-item nothing hide">
                @component('components.nothing')
                    @slot('size', 'small')
                    @slot('text', 'Henüz hiç arama yapmadı!')
                @endcomponent
            </li>
            <li data-name="item" class="collection-item model hide">
                <code data-name="query"></code>
                <div class="d-flex justify-content-between">
                    <time data-name="created-at" class="timeago grey-text d-table"></time>
                    <time data-name="deleted-at" class="timeago red-text d-table"></time>
                </div>
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
                id="history-more_button"
                type="button"
                data-json-target="#history">Öncekiler</button>
    </div>
@endsection

@section('dock')
    @include('user.admin._menu', [ 'active' => 'search_history', 'id' => $user->id ])
@endsection
