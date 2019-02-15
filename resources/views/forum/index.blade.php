@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => @$title ?
    [
        [
            'text' => 'Forum',
            'link' => route('forum.index')
        ],
        [
            'text' => $title
        ]
    ]
    :
    (
        @$category ?
        [
            [
                'text' => 'Forum',
                'link' => route('forum.index')
            ],
            [
                'text' => $category->name
            ]
        ]
        :
        [
            [
                'text' => 'Forum'
            ]
        ]
    ),
    'dock' => true
])

@section('wildcard')
    <div class="card wild-background">
        @auth
            @if (@$category->lock == false || auth()->user()->root())
                <div class="card-image">
                    <a href="{{ route('forum.thread.form') }}" class="btn-floating btn-large halfway-fab waves-effect white" data-tooltip="Konu Başlat" data-position="left">
                        <i class="material-icons grey-text text-darken-2">add</i>
                    </a>
                </div>
            @endif
        @else
            <div class="card-image">
                <a href="{{ route('user.login') }}" class="btn-floating btn-large halfway-fab waves-effect white" data-tooltip="Giriş Yap" data-position="left">
                    <i class="material-icons grey-text text-darken-2">person</i>
                </a>
            </div>
        @endauth
        <div class="container">
            <span class="wildcard-title white-text">
                @isset ($title)
                    {{ $title }}
                @else
                    @isset ($category)
                        {{ $category->name }}
                    @else
                        {{ 'Forum' }}
                    @endif
                @endif
            </span>
        </div>
    </div>
@endsection

@isset ($category)
    @push('external.include.header')
        <meta name="description" content="{{ str_limit($category->description, 255) }}" />

        <meta property="og:title" content="{{ $category->name }}">
        <meta property="og:description" content="{{ str_limit($category->description, 255) }}" />
        <meta property="og:type" content="category" />
        <meta property="og:url" content="{{ url()->full() }}" />
        <meta property="og:image" content="{{ asset('img/olive-twitter-card.png?v='.config('system.version')) }}" />

        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:site" content="{{ url()->full() }}" />
        <meta name="twitter:title" content="{{ $category->name }}" />
        <meta name="twitter:description" content="{{ str_limit($category->description, 255) }}" />
        <meta name="twitter:image" content="{{ asset('img/olive-twitter-card.png?v='.config('system.version')) }}" />
    @endpush
@endisset

@section('content')
    {!! $data->links('vendor.pagination.materializecss_simple') !!}

    <ul class="card">
        <li class="card-content grey lighten-5">
            @isset ($category)
                <div class="chip">{{ $category->name }}</div>
            @else
                <div class="chip">{{ @$title ? $title : 'Tüm Konular' }}</div>
            @endisset
        </li>
        @forelse ($data as $thread)
            @php
                $color_light = '';
                $color_dark = '';

                if ($thread->static)
                {
                    $color_light = 'white lighten-2 grey-text';
                    $color_dark = 'grey lighten-2';
                }
            @endphp
        <li class="card-content z-depth-1 hoverable {{ $color_dark }}">
            <div class="d-flex">
                <span class="align-self-center center-align" style="margin: 0 1rem 0 0;">
                    <a class="d-block" data-tooltip="{{ $thread->user->name }}" data-position="right" href="{{ route('user.profile', $thread->user_id) }}">
                        <img alt="Avatar" src="{{ $thread->user->avatar() }}" class="circle" style="width: 64px; height: 64px;" />
                    </a>
                </span>
                <div class="align-self-center">
                    <div class="d-flex">
                        @if ($thread->closed)
                            <i class="material-icons grey-text text-darken-2 tiny" data-tooltip="Kapalı">lock</i>
                        @endif

                        @if ($thread->static)
                            <i class="material-icons grey-text text-darken-2 tiny" data-tooltip="Sabit">terrain</i>
                        @endif

                        @if ($thread->question == 'solved')
                            <i class="material-icons grey-text text-darken-2 tiny" data-tooltip="Çözüldü">check</i>
                        @elseif ($thread->question == 'unsolved')
                            <i class="material-icons grey-text text-darken-2 tiny" data-tooltip="Soru">help</i>
                        @endif
                    </div>
                    <a href="{{ $thread->route() }}" class="d-flex">
                        <span class="card-title card-title-small align-self-center mb-0">{{ $thread->subject }}</span>
                    </a>
                    <p class="grey-text">
                        @if (count($thread->replies))
                            <time class="timeago grey-text text-darken-2" data-time="{{ $thread->updated_at }}">{{ date('d.m.Y H:i', strtotime($thread->updated_at)) }}</time>
                            <span>yanıtladı</span>
                            <a href="{{ route('user.profile', $thread->replies->last()->user->id) }}">{{ '@'.$thread->replies->last()->user->name }}</a>
                        @else
                            <time class="timeago grey-text text-darken-2" data-time="{{ $thread->created_at }}">{{ date('d.m.Y H:i', strtotime($thread->created_at)) }}</time>
                            <span>yazdı</span>
                            <a href="{{ route('user.profile', $thread->user_id) }}">{{ '@'.$thread->user->name }}</a>
                        @endif
                    </p>
                    @if (count($thread->replies))
                        {!! $thread->replies()->paginate(10)->onEachSide(1)->setPath($thread->route())->links('vendor.pagination.materializecss_in') !!}
                    @endif
                </div>
                <div class="align-self-center ml-auto d-flex flex-column">
                    <a href="{{ route('forum.category', $thread->category->slug) }}" class="chip waves-effect center-align {{ $color_light }}">{{ $thread->category->name }}</a>
                    <span class="badge d-flex grey-text justify-content-end">
                        <span class="align-self-center">{{ count($thread->replies) }}</span>
                        <i class="material-icons align-self-center" style="margin: 0 0 0 .4rem;">reply</i>
                    </span>
                    <span class="badge d-flex grey-text justify-content-end">
                        <span class="align-self-center">{{ $thread->hit }}</span>
                        <i class="material-icons align-self-center" style="margin: 0 0 0 .4rem;">remove_red_eye</i>
                    </span>
                </div>
            </div>
        </li>
        @empty
        <li class="card-content grey-text">
            Üzgünüm, daha fazla içerik yok.
        </li>
        @endforelse
    </ul>

    {!! $data->links('vendor.pagination.materializecss_simple') !!}
@endsection

@auth
    @if (auth()->user()->root())
        @push('local.scripts')
            function cat_modal()
            {
                var mdl = modal({
                    'id': 'category',
                    'body': $('<form />', {
                        'action': '{{ route('admin.forum.category') }}',
                        'id': 'category-form',
                        'class': 'json',
                        'html': [
                            $('<div />', {
                                'class': 'input-field',
                                'html': [
                                    $('<input />', {
                                        'id': 'name',
                                        'name': 'name',
                                        'type': 'text',
                                        'class': 'validate',
                                        'data-length': 16,
                                        'data-slug': '#slug'
                                    }),
                                    $('<label />', {
                                        'for': 'name',
                                        'html': 'Kategori Adı'
                                    }),
                                    $('<span />', {
                                        'class': 'helper-text',
                                        'html': 'Kısa ve öz bir forum kategori adı girin.'
                                    })
                                ]
                            }),
                            $('<div />', {
                                'class': 'input-field',
                                'html': [
                                    $('<input />', {
                                        'id': 'slug',
                                        'name': 'slug',
                                        'type': 'text',
                                        'class': 'validate',
                                        'data-length': 32
                                    }),
                                    $('<label />', {
                                        'for': 'slug',
                                        'html': 'Slug'
                                    }),
                                    $('<span />', {
                                        'class': 'helper-text',
                                        'html': 'Kategori adını slug formatında girin.'
                                    })
                                ]
                            }),
                            $('<div />', {
                                'class': 'input-field',
                                'html': [
                                    $('<input />', {
                                        'id': 'description',
                                        'name': 'description',
                                        'type': 'text',
                                        'class': 'validate',
                                        'data-length': 255
                                    }),
                                    $('<label />', {
                                        'for': 'description',
                                        'html': 'Açıklama'
                                    }),
                                    $('<span />', {
                                        'class': 'helper-text',
                                        'html': 'Kategori hakkında detaylı bir açıklama girin.'
                                    })
                                ]
                            }),
                            $('<div />', {
                                'class': 'switch mb-2',
                                'html': $('<label>', {
                                    'html': [
                                        $('<span />', {
                                            'html': 'Açık'
                                        }),
                                        $('<input />', {
                                            'type': 'checkbox',
                                            'value': 'on',
                                            'name': 'lock',
                                            'id': 'lock'
                                        }),
                                        $('<span />', {
                                            'class': 'lever'
                                        }),
                                        $('<span />', {
                                            'html': 'Kilitli'
                                        })
                                    ]
                                })
                            })
                        ]
                    }),
                    'size': 'modal-medium',
                    'options': {
                        dismissible: false
                    },
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
                           'data-trigger': 'delete_cat',
                           'href': '#',
                           'class': 'waves-effect btn-flat grey-text hide',
                           'html': buttons.remove
                       }),
                       $('<span />', {
                           'html': ' '
                       }),
                       $('<button />', {
                           'type': 'submit',
                           'class': 'waves-effect btn-flat cyan-text',
                           'data-submit': 'form#category-form',
                           'html': buttons.ok
                       })
                    ]
                });

                return mdl;
            }

            $(document).on('click', '[data-trigger=delete_cat]', function() {
                var mdl = modal({
                        'id': 'alert',
                        'body': $('<span />', {
                            'class': 'red-text',
                            'html': 'Kategori silinirse, altındaki tüm içerikler de silinecektir. Yine de silme işlemine devam etmek istiyor musunuz?'
                        }),
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
                                'data-href': '{{ route('admin.forum.category') }}',
                                'data-method': 'delete',
                                'data-id': $(this).data('id'),
                                'data-callback': '__delete_cat'
                            })
                        ]
                    });
            })

            $(document).on('click', '[data-trigger=create-cat]', function() {
                var _modal = cat_modal();
                    _modal.find('.modal-title').html('Kategori Oluştur')

                var form = _modal.find('form#category-form')

                $('input[name=name]').val('').characterCounter()
                $('input[name=slug]').val('').characterCounter()
                $('input[name=lock]').prop('checked', false)
                $('input[name=description]').val('').characterCounter()

                $('[data-trigger=delete_cat]').removeAttr('data-id').addClass('hide')

                form.removeAttr('data-id')
                form.attr('method', 'put')
                form.data('callback', '__create_cat')

                M.updateTextFields()
            })

            function __delete_cat(__, obj)
            {
                if (obj.status == 'ok')
                {
                    $('#modal-alert').modal('close')

                    setTimeout(function() {
                        $('#modal-category').modal('close')
                    }, 200)

                    $('[data-id=cat-' + obj.data.id + ']').remove()

                    vzAjax($('#categories'))

                    M.toast({
                        html: 'Kategori silindi.',
                        classes: 'green darken-2'
                    })
                }
            }

            function __update_cat(__, obj)
            {
                if (obj.status == 'ok')
                {
                    M.toast({
                        html: 'Kategori Güncellendi',
                        classes: 'green darken-2'
                    })

                    var category = $('[data-id=cat-' + obj.data.id + ']');
                        category.find('[data-name=name]').html(obj.data.name).attr('href', obj.data.url)
                        category.find('[data-name=description]').html(obj.data.description)

                    $('#modal-category').modal('close')
                }
            }

            function __create_cat(__, obj)
            {
                if (obj.status == 'ok')
                {
                    M.toast({
                        html: 'Kategori Oluşturuldu',
                        classes: 'green darken-2'
                    })

                    vzAjax($('#categories'))

                    $('#modal-category').modal('close')
                }
            }

            function __get_cat(__, obj)
            {
                if (obj.status == 'ok')
                {
                    var _modal = cat_modal();
                        _modal.find('.modal-title').html('Kategori Güncelle')

                    var form = _modal.find('form#category-form')

                    $('input[name=name]').val(obj.data.name).characterCounter()
                    $('input[name=slug]').val(obj.data.slug).characterCounter()
                    $('input[name=lock]').prop('checked', obj.data.lock ? true : false)
                    $('input[name=description]').val(obj.data.description).characterCounter()

                    $('[data-trigger=delete_cat]').data('id', obj.data.id).removeClass('hide')

                    form.data('id', obj.data.id)
                    form.attr('method', 'patch')
                    form.data('callback', '__update_cat')

                    M.updateTextFields()
                }
            }
        @endpush
    @endif
@endauth

@push('local.scripts')
    function __forum_categories(__, obj)
    {
        var ul = $('#categories');
        var item_model = ul.children('.model');

        if (obj.status == 'ok')
        {
            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var selector = $('[data-id=cat-' + o.id + '].collection-item'),

                        item = selector.length ? selector : item_model.clone();
                        item.removeClass('model hide')
                            .addClass('_tmp')
                            .attr('data-id', 'cat-' + o.id)

                        item.find('[data-name=name]').html(o.name).attr('href', o.url)
                        item.find('[data-name=description]').html(o.description)

                        @auth
                            @if (auth()->user()->root())
                                item.find('[data-name=edit-button]').attr('data-id', o.id)
                            @endif
                        @endauth

                        if (!selector.length)
                        {
                            item.appendTo(ul)
                        }
                })
            }

            $('#cat-loader').hide()
        }
    }
@endpush

@section('dock')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Kategoriler</span>
        </div>

        @auth
            @if (auth()->user()->root())
                <div class="card-image">
                    <a href="#" class="btn-floating halfway-fab waves-effect white" data-trigger="create-cat">
                        <i class="material-icons grey-text text-darken-2">add</i>
                    </a>
                </div>

                <div class="card-content white">Bu alan sadece root yetkisine sahip kullanıcılarda görünür.</div>
            @endif
        @endauth

        <div class="card-tabs">
            <ul class="tabs cyan tabs-transparent tabs-fixed-width">
                <li class="tab">
                    <a href="#categories" class="active">Kategori</a>
                </li>
                <li class="tab">
                    <a href="#criterion">Tür</a>
                </li>
            </ul>
        </div>

        <ul id="categories"
            class="collection white load mb-0" 
            data-href="{{ route('forum.categories') }}"
            data-callback="__forum_categories"
            data-method="post"
            data-nothing>
            <li class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </li>
            <li class="collection-item model hide justify-content-between">
                <p class="d-flex justify-content-between mb-0">
                    <a href="#" data-name="name" class="align-self-center mr-auto"></a>
                    @auth
                        @if (auth()->user()->root())
                            <a
                                href="#"
                                class="btn-floating btn-small waves-effect white json align-self-center"
                                data-href="{{ route('admin.forum.category') }}"
                                data-method="post"
                                data-callback="__get_cat"
                                data-name="edit-button">
                                <i class="material-icons grey-text text-darken-2 tiny">create</i>
                            </a>
                        @endif
                    @endauth
                </p>
                <span class="grey-text d-block" data-name="description"></span>
            </li>
        </ul>

        @component('components.loader')
            @slot('color', 'cyan')
            @slot('id', 'cat-loader')
            @slot('class', 'm-0')
        @endcomponent

        <div class="collection white" id="criterion" style="display: none;">
            <a href="{{ route('forum.index') }}" class="collection-item waves-effect d-flex">
                <i class="material-icons" style="margin: 0 .4rem 0 0;">library_books</i>
                <span class="align-self-center">Tüm Konular</span>
            </a>

            @auth
                @if (auth()->user()->moderator || auth()->user()->root)
                    <a href="{{ route('forum.group', [ __('route.forum.popular'), __('route.forum.spam') ]) }}" class="collection-item waves-effect d-flex red-text">
                        <i class="material-icons" style="margin: 0 .4rem 0 0;">bug_report</i>
                        <span class="align-self-center">Spam Sıralaması</span>
                    </a>
                @endif
            @endauth

            <div class="divider"></div>

            @auth
                <a href="{{ route('forum.group', [ __('route.forum.thread'), __('route.forum.my_threads') ]) }}" class="collection-item waves-effect green-text d-flex">
                    <i class="material-icons" style="margin: 0 .4rem 0 0;">local_library</i>
                    <span class="align-self-center">Açılan Konular</span>
                </a>
                <a href="{{ route('forum.group', [ __('route.forum.thread'), __('route.forum.included_threads') ]) }}" class="collection-item waves-effect green-text d-flex">
                    <i class="material-icons" style="margin: 0 .4rem 0 0;">library_add</i>
                    <span class="align-self-center">Dahil Olunan Konular</span>
                </a>
                <a href="{{ route('forum.group', [ __('route.forum.thread'), __('route.forum.followed_threads') ]) }}" class="collection-item waves-effect green-text d-flex">
                    <i class="material-icons" style="margin: 0 .4rem 0 0;">star</i>
                    <span>Takip Edilen Konular</span>
                </a>

                <div class="divider"></div>
            @endauth

            <a href="{{ route('forum.group', [ __('route.forum.question'), __('route.forum.unanswered') ]) }}" class="collection-item waves-effect d-flex">
                <i class="material-icons" style="margin: 0 .4rem 0 0;">reply</i>
                <span class="align-self-center">Yanıtlanmayan Sorular</span>
            </a>
            <a href="{{ route('forum.group', [ __('route.forum.question'), __('route.forum.solved') ]) }}" class="collection-item waves-effect d-flex">
                <i class="material-icons" style="margin: 0 .4rem 0 0;">check</i>
                <span class="align-self-center">Çözülen Sorular</span>
            </a>
            <a href="{{ route('forum.group', [ __('route.forum.question'), __('route.forum.unsolved') ]) }}" class="collection-item waves-effect d-flex">
                <i class="material-icons" style="margin: 0 .4rem 0 0;">close</i>
                <span class="align-self-center">Çözülmeyen Sorular</span>
            </a>

            <div class="divider"></div>

            <a href="{{ route('forum.group', [ __('route.forum.popular'), __('route.forum.week') ]) }}" class="collection-item waves-effect d-flex">
                <i class="material-icons" style="margin: 0 .4rem 0 0;">star</i>
                <span class="align-self-center">Haftanın Popülerleri</span>
            </a>
            <a href="{{ route('forum.group', [ __('route.forum.popular'), __('route.forum.all_time') ]) }}" class="collection-item waves-effect d-flex">
                <i class="material-icons" style="margin: 0 .4rem 0 0;">star</i>
                <span class="align-self-center">Tüm Zamanların Popülerleri</span>
            </a>
        </div>
    </div>
@endsection

@push('local.scripts')
    $(document).ready(function() {
        $('.tabs').tabs()
    })
@endpush
