@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Forum',
            'link' => route('forum.index')
        ],
        [
            'text' => $thread->category->name,
            'link' => route('forum.category', $thread->category->slug)
        ],
        [
            'text' => $thread->subject
        ]
    ]
])

@push('external.include.header')
    <meta name="description" content="{{ str_limit($thread->body, 255) }}" />

    <meta property="og:title" content="{{ $thread->subject }}">
    <meta property="og:description" content="{{ str_limit($thread->body, 255) }}" />
    <meta property="og:type" content="article" />
    <meta property="og:url" content="{{ url()->full() }}" />
    <meta property="og:image" content="{{ asset('img/olive-twitter-card.png?v='.config('system.version')) }}" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:site" content="{{ url()->full() }}" />
    <meta name="twitter:title" content="{{ $thread->subject }}" />
    <meta name="twitter:description" content="{{ str_limit($thread->body, 255) }}" />
    <meta name="twitter:image" content="{{ asset('img/olive-twitter-card.png?v='.config('system.version')) }}" />

    <link rel="stylesheet" href="{{ asset('css/highlight.min.css?v='.config('system.version')) }}" />
@endpush

@auth
    @section('action-bar')
        <a
            href="#"
            class="btn-floating btn-large halfway-fab waves-effect white {{ $thread->closed ? 'hide' : '' }}"
            data-id="{{ $thread->id }}"
            data-button="reply"
            data-tooltip="Cevapla"
            data-position="left">
            <i class="material-icons grey-text text-darken-2">reply</i>
        </a>
    @endsection
@else
    @section('action-bar')
        <a href="{{ route('user.login') }}" class="btn-floating btn-large halfway-fab waves-effect white" data-tooltip="Giriş Yap" data-position="left">
            <i class="material-icons grey-text text-darken-2">person</i>
        </a>
    @endsection
@endauth

@section('wildcard')
    <div class="card wild-background">
        <div class="container">
            <span class="wildcard-title">{{ $thread->subject }}</span>
        </div>
    </div>
@endsection

@push('local.scripts')
    function __vote(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('[data-id=message-' + obj.data.id + ']').find('[data-name=vote]').html(obj.data.vote)
        }
    }

    @auth
        @if (auth()->user()->root || auth()->user()->moderator)
            $(document).on('click', '[data-trigger=delete]', function() {
                var __ = $(this);

                var mdl = modal({
                        'id': 'delete',
                        'body': $('<span />', {
                            'class': 'red-text',
                            'html': __.data('thread') ? 'Sileceğiniz konu altındaki tüm mesajlar silinecek. Yine de silmek istiyor musunuz?' : 'Bu mesajı silmek istediğinizden emin misiniz?'
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
                                'class': 'waves-effect btn-flat json',
                                'html': buttons.ok,
                                'data-href': '{{ route('forum.message.delete') }}',
                                'data-id': __.data('id'),
                                'data-method': 'delete',
                                'data-callback': '__delete'
                            })
                        ]
                    });
            })

            function __delete(__, obj)
            {
                if (obj.status == 'ok')
                {
                    if (obj.data.status == 'thread')
                    {
                        window.location = '{{ route('forum.index') }}';
                    }
                    else
                    {
                        $('[data-id=message-' + __.data('id') + ']').slideUp(400)

                        $('[data-name=message]').find('[data-icon=solved]').addClass('hide')
                        $('[data-name=message]').find('[data-icon=unsolved]').removeClass('hide')
                    }

                    $('#modal-delete').modal('close')
                }
            }

            function __close(__, obj)
            {
                if (obj.status == 'ok')
                {
                    if (obj.data.status == 'closed')
                    {
                        __.html('Konuyu Aç')

                        $('[data-id=message-' + obj.data.id + ']').find('[data-icon=closed]').removeClass('hide')
                        $('[data-button=reply]').addClass('hide')
                        $('[data-section=reply]').addClass('hide')

                        M.toast({ html: 'Konu Kapandı', classes: 'red darken-2' })
                    }
                    else if (obj.data.status == 'open')
                    {
                        __.html('Konuyu Kapat')

                        $('[data-id=message-' + obj.data.id + ']').find('[data-icon=closed]').addClass('hide')
                        $('[data-button=reply]').removeClass('hide')
                        $('[data-section=reply]').removeClass('hide')

                        M.toast({ html: 'Konu Açıldı', classes: 'green darken-2' })
                    }
                }
            }

            function __static(__, obj)
            {
                if (obj.status == 'ok')
                {
                    if (obj.data.status == 'static')
                    {
                        __.html('Sabitliği Kaldır')

                        $('[data-id=message-' + obj.data.id + ']').find('[data-icon=static]').removeClass('hide')

                        M.toast({ html: 'Konu Sabitlendi', classes: 'green darken-2' })
                    }
                    else if (obj.data.status == 'unstatic')
                    {
                        __.html('Konuyu Sabitle')

                        $('[data-id=message-' + obj.data.id + ']').find('[data-icon=static]').addClass('hide')

                        M.toast({ html: 'Konunun Sabitliği Kaldırıldı', classes: 'green darken-2' })
                    }
                }
            }

            function __move(__, obj)
            {
                if (obj.status == 'ok')
                {
                    $('#modal-move').modal('close')

                    location.reload();
                }
            }

            function __move_trigger(__, obj)
            {
                if (obj.status == 'ok')
                {
                    var mdl = modal({
                        'title': 'Taşı',
                        'id': 'move',
                        'body': $('<form />', {
                            'action': '{{ route('forum.thread.move') }}',
                            'id': 'move-form',
                            'class': 'json',
                            'data-method': 'post',
                            'data-callback': '__move',
                            'data-id': __.data('id'),
                            'html': [
                                $('<div />', {
                                    'class': 'collection collection-unstyled',
                                    'data-name': 'categories'
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
                            $('<a />', {
                                'data-trigger': 'delete',
                                'href': '#',
                                'class': 'waves-effect btn-flat grey-text hide',
                                'html': buttons.remove
                            }),
                            $('<span />', {
                                'html': ' '
                            }),
                            $('<button />', {
                                'type': 'submit',
                                'class': 'waves-effect btn-flat',
                                'data-submit': 'form#move-form',
                                'html': buttons.ok
                            })
                        ],
                        'size': 'modal-medium',
                        'options': {
                            dismissible: false
                        }
                    });
                }

                var collection = $('[data-name=categories]');

                $.each(obj.hits, function(key, o) {
                    collection.append(
                        $('<label />', {
                            'class': 'collection-item waves-effect d-block',
                            'html': [
                                $('<input />', {
                                    'name': 'category_id',
                                    'id': 'category-' + o.id,
                                    'value': o.id,
                                    'type': 'radio'
                                }),
                                $('<span />', {
                                    'html': o.name
                                })
                            ]
                        })
                    )
                })
            }
        @endif

        function __follow(__, obj)
        {
            if (obj.status == 'ok')
            {
                if (obj.data.status == 'follow')
                {
                    __.html('Takibi Bırak')

                    M.toast({ html: 'Konu Takip Ediliyor', classes: 'green darken-2' })
                }
                else if (obj.data.status == 'unfollow')
                {
                    __.html('Takip Et')

                    M.toast({ html: 'Takibi Bıraktınız', classes: 'red darken-2' })
                }
            }
        }

        @if ($thread->authority())
            function __best_answer(__, obj)
            {
                if (obj.status == 'ok')
                {
                    $('[data-name=message]').find('[data-icon=solved]').addClass('hide')
                    $('[data-name=message]').find('[data-icon=unsolved]').addClass('hide')
                    $('[data-name=message]').find('[data-icon=check]').addClass('hide')
                    
                    $('[data-id=message-' + obj.data.id + ']').find('[data-icon=check]').removeClass('hide')
                    $('[data-id=message-' + obj.data.thread_id + ']').find('[data-icon=solved]').removeClass('hide')
                }
            }
        @endif

        $(document).on('click', '[data-trigger=spam]', function() {
            var __ = $(this);

            var mdl = modal({
                    'id': 'spam',
                    'body': 'Bu mesajın spam olduğuna emin misiniz?',
                    'size': 'modal-small',
                    'title': 'Spam Bildir',
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
                            'class': 'waves-effect btn-flat json',
                            'html': buttons.ok,
                            'data-href': '{{ route('forum.message.spam') }}',
                            'data-id': __.data('id'),
                            'data-method': 'post',
                            'data-callback': '__spam'
                        })
                    ]
                });
        })

        function __spam(__, obj)
        {
            if (obj.status == 'ok')
            {
                M.toast({ html: 'Mesaj spam olarak işaretlendi.' })

                $('#modal-spam').modal('close')
            }
        }

        $('[data-button=reply]').click(function() {
            var __ = $(this);

            _scrollTo({
                'target': '[data-section=reply]',
                'tolerance': '-96px'
            })

            $('input[name=reply_id]').val(__.data('id'))

            if (__.data('quote') && __.data('quote') != {{ $thread->id }})
            {
                var markdown = eval(element('[data-original-source=' + __.data('quote') + ']'));

                $('[data-section=quote]').removeClass('hide').find('.markdown').html(markdown.html())
            }
            else
            {
                $('[data-section=quote]').addClass('hide')
            }

            setTimeout(function() {
                $('textarea[name=body]').focus()
            }, 500)
        })

        $(document).on('click', '[data-section=quote] [data-name=close]', function() {
            var __ = $(this);
                __.closest('.card-content').addClass('hide')
            $('input[name=reply_id]').val('{{ $thread->id }}')
        })

        function __submit(__, obj)
        {
            if (obj.status == 'ok')
            {
                M.toast({ html: 'Cevap Ekleniyor...', classes: 'green darken-2' })

                $('textarea[name=body]').val('')
                $('input[name=reply_id]').val('{{ $thread->id }}')

                history.pushState(null, null, '{{ $thread->route() }}?page=' + obj.data.last_page + '#message-' + obj.data.id);
                location.reload()
            }
        }

        function __preview(__, obj)
        {
            if (obj.status == 'ok')
            {
                __.children('.markdown').html(obj.data.message)

                $('code').each(function(i, block) {
                    hljs.highlightBlock(block);
                })
            }
        }

        $(document).ready(function() {
            $('.tabs').tabs({
                onShow: function(e) {
                    if (e.id == 'preview')
                    {
                        vzAjax($('#preview'))
                    }
                }
            })

            $('input[name=subject], textarea[name=body]').characterCounter()
        })

        function __reply_update(__, obj)
        {
            if (obj.status == 'ok')
            {
                var original_source = $('[data-original-source=' + obj.data.id + ']');
                    original_source.removeClass('hide')
                    original_source.html(obj.data.body)

                var live_form = original_source.next('#reply-form-' + obj.data.id);
                    live_form.addClass('hide')

                M.toast({ html: 'Cevap Güncellendi', classes: 'green darken-2' })

                $('code').each(function(i, block) {
                    hljs.highlightBlock(block);
                })
            }
        }

        $(document).on('click', '[data-trigger=reply_get-cancel]', function() {
            var __ = $(this);

            var original_source = $('[data-original-source=' + __.data('id') + ']');
                original_source.removeClass('hide')

            var live_form = original_source.next('#reply-form-' + __.data('id'));
                live_form.addClass('hide')
        })

        function __reply_get(__, obj)
        {
            if (obj.status == 'ok')
            {
                var form = $('<form />', {
                    'id': 'reply-form-' + obj.data.id,
                    'class': 'json',
                    'method': 'post',
                    'action': '{{ route('forum.reply.update') }}',
                    'data-id': obj.data.id,
                    'data-callback': '__reply_update',
                    'html': $('<div />', {
                        'class': 'card',
                        'html': [
                            $('<div />', {
                                'class': 'card-content textarea-content',
                                'html': $('<div />', {
                                    'class': 'input-field',
                                    'html': [
                                        $('<textarea />', {
                                            'id': 'body-' + obj.data.id,
                                            'name': 'body-' + obj.data.id,
                                            'data-alias': 'body',
                                            'class': 'materialize-textarea validate',
                                            'data-length': 5000,
                                            'val': obj.data.body
                                        }),
                                        $('<label />', {
                                            'for': 'body-' + obj.data.id,
                                            'html': 'Cevap İçeriği'
                                        }),
                                        $('<div />', {
                                            'class': 'helper-text'
                                        }),
                                        $('<small />', {
                                            'class': 'grey-text',
                                            'html': 'Bu alanda <a href="https://guides.github.com/features/mastering-markdown/" target="_blank">Markdown</a> kullanabilirsiniz.'
                                        })
                                    ]
                                })
                            }),
                            $('<div />', {
                                'class': 'card-action right-align',
                                'html': [
                                    $('<button />', {
                                        'type': 'button',
                                        'data-trigger': 'reply_get-cancel',
                                        'data-id': obj.data.id,
                                        'class': 'btn-flat waves-effect',
                                        'html': $('<i />', {
                                            'class': 'material-icons',
                                            'html': 'close'
                                        })
                                    }),
                                    $('<span />', {
                                        'html': ' '
                                    }),
                                    $('<button />', {
                                        'type': 'submit',
                                        'class': 'btn-flat waves-effect',
                                        'html': $('<i />', {
                                            'class': 'material-icons',
                                            'html': 'check'
                                        })
                                    })
                                ]
                            })
                        ]
                    })
                })

                var original_source = $('[data-original-source=' + obj.data.id + ']');
                    original_source.addClass('hide')

                var live_form = original_source.next('#reply-form-' + obj.data.id);

                if (live_form.length)
                {
                    live_form.removeClass('hide')
                    live_form.find('textarea[name=body]').val(obj.data.body)
                }
                else
                {
                    original_source.after(form)
                }

                $('textarea[name=body]').characterCounter()

                M.textareaAutoResize($('textarea#body-' + obj.data.id))
            }
        }
    @endauth

    var hash = window.location.hash.substr(1);

    if (hash)
    {
        $(window).on('load', function() {
            _scrollTo({
                'target': '#' + hash,
                'tolerance': '-72px'
            })

            $('[data-id=' + hash + ']').hide().show('highlight', {}, 4000)
        })
    }

    $('code').each(function(i, block) {
        hljs.highlightBlock(block);
    })
@endpush

@section('content')
    @php
        $last_user_id = 0;
    @endphp

    <div data-section="messages">
        <div class="card mb-1">
            @forelse ($messages as $message)
                @auth
                    <ul id="thread-menu-{{ $message->id }}" class="dropdown-content">
                        <li data-button="reply" data-quote="{{ $message->id }}" data-id="{{ $message->id }}" class="{{ $thread->closed ? 'hide' : '' }}">
                            <a href="#" class="waves-effect">Cevapla</a>
                        </li>
                        @if ($message->message_id)
                            <li>
                                <a
                                    href="#"
                                    class="waves-effect json"
                                    data-callback="__reply_get"
                                    data-method="post"
                                    data-href="{{ route('forum.reply.get', $message->id) }}">Düzenle</a>
                            </li>
                        @else
                            @if ($message->authority())
                               <li>
                                   <a href="{{ route('forum.thread.form', $thread->id) }}" class="waves-effect">Düzenle</a>
                               </li>
                            @endif
                            @if ($message->authority(false))
                                <li>
                                    <a href="#" class="waves-effect json" data-href="{{ route('forum.thread.status') }}" data-id="{{ $thread->id }}" data-method="post" data-callback="__close">{{ $message->closed ? 'Konuyu Aç' : 'Konuyu Kapat' }}</a>
                                </li>
                                <li>
                                    <a href="#" class="waves-effect json" data-href="{{ route('forum.thread.static') }}" data-id="{{ $thread->id }}" data-method="post" data-callback="__static">{{ $message->static ? 'Sabitliği Kaldır' : 'Konuyu Sabitle' }}</a>
                                </li>
                                <li>
                                    <a href="#" class="waves-effect json" data-href="{{ route('forum.categories') }}" data-method="post" data-callback="__move_trigger" data-id="{{ $thread->id }}">Taşı</a>
                                </li>
                            @endif

                            <li>
                                <a href="#" class="waves-effect json" data-href="{{ route('forum.thread.follow') }}" data-id="{{ $thread->id }}" data-method="post" data-callback="__follow">
                                    {{ $thread->followers()->where('user_id', auth()->user()->id)->exists() ? 'Takibi Bırak' : 'Takip Et' }}
                                </a>
                            </li>
                        @endif

                        @if (auth()->user()->id != $message->user_id)
                            <li>
                                <a href="#" class="waves-effect" data-trigger="spam" data-id="{{ $message->id }}">Spam Bildir</a>
                            </li>
                        @endif

                        @if (@$message->thread->question)
                            @if ($message->authority())
                                <li>
                                    <a href="#" class="waves-effect json" data-href="{{ route('forum.message.best') }}" data-id="{{ $message->id }}" data-method="post" data-callback="__best_answer">En İyi Cevap</a>
                                </li>
                            @endif
                        @endif

                        @if ($message->authority(false))
                            <li>
                                <a href="#" class="waves-effect" data-trigger="delete" data-id="{{ $message->id }}" data-thread="{{ $message->message_id ? 'false' : 'true' }}">Sil</a>
                            </li>
                        @endif
                    </ul>
                @endauth

                @if ($last_user_id && $last_user_id != $message->user_id)
                    </div>
                    <div class="card mb-1">
                @endif
                <div
                    id="message-{{ $message->id }}"
                    data-name="message"
                    data-id="message-{{ $message->id }}"
                    class="card-content grey lighten-5 @if ($last_user_id && $last_user_id != $message->user_id){{ 'z-depth-1' }}@endif">
                    <div class="d-flex">
                        <span class="align-self-center center-align" style="margin: 0 1rem 0 0;">
                            @if ($last_user_id != $message->user_id)
                                <a class="d-block" href="{{ route('user.profile', $message->user_id) }}">
                                    <img alt="Avatar" src="{{ $message->user->avatar() }}" class="circle" style="width: 64px; height: 64px;" />
                                </a>
                            @endif
                        </span>
                        <div class="align-self-center">
                            <div class="d-flex">
                                @if ($message->message_id)
                                    <i data-icon="check" class="material-icons grey-text text-darken-2 {{ $message->question == 'check' ? '' : 'hide' }}" data-tooltip="En İyi Cevap">check</i>
                                @else
                                    <i data-icon="closed" class="material-icons grey-text text-darken-2 {{ $message->closed ? '' : 'hide' }}" data-tooltip="Kapalı">lock</i>
                                    <i data-icon="static" class="material-icons grey-text text-darken-2 {{ $message->static ? '' : 'hide' }}" data-tooltip="Sabit">terrain</i>

                                    <i data-icon="solved" class="material-icons grey-text text-darken-2 {{ $message->question == 'solved' ? '' : 'hide' }}" data-tooltip="Çözüldü">check</i>
                                    <i data-icon="unsolved" class="material-icons grey-text text-darken-2 {{ $message->question == 'unsolved' ? '' : 'hide' }}" data-tooltip="Soru">help</i>
                                @endif
                            </div>
                            @if ($last_user_id != $message->user_id)
                                <a
                                    style="@if ($message->user->banned_at){{ 'text-decoration: line-through;' }}@endif"
                                    class="card-title card-title-small align-self-center mb-0"
                                    href="{{ route('user.profile', $message->user_id) }}">{{ $message->user->name }}</a>
                            @endif
                            <time class="timeago grey-text text-darken-2" data-time="{{ $message->created_at }}">{{ date('d.m.Y H:i', strtotime($message->created_at)) }}</time>
                        </div>
                        <div class="align-self-center ml-auto d-flex flex-column">
                            @auth
                                <a style="margin-bottom: .4rem;" class="btn-floating btn-flat waves-effect ml-auto dropdown-trigger" data-target="thread-menu-{{ $message->id }}" data-align="right">
                                    <i class="material-icons">more_vert</i>
                                </a>
                            @endauth
                            @if ($last_user_id != $message->user_id)
                                @if ($message->user->badges->count())
                                    <div class="d-flex justify-content-end mb-1">
                                        @foreach ($message->user->badges()->limit(3)->orderBy('badge_id', 'DESC')->get() as $badge)
                                            <img
                                                alt="{{ config('system.user.badges')[$badge->badge_id]['name'] }}"
                                                src="{{ asset(config('system.user.badges')[$badge->badge_id]['image_src']) }}"
                                                data-tooltip="{{ config('system.user.badges')[$badge->badge_id]['name'] }}"
                                                style="width: 32px; height: 32px;" />
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                            @if (!$message->message_id)
                                <span class="badge d-flex justify-content-end grey-text text-darken-2">
                                    <span class="align-self-center">{{ count($message->replies) }}</span>
                                    <i class="material-icons align-self-center" style="margin: 0 0 0 .4rem;">reply</i>
                                </span>
                                <span class="badge d-flex justify-content-end grey-text text-darken-2">
                                    <span class="align-self-center">{{ $message->hit }}</span>
                                    <i class="material-icons align-self-center" style="margin: 0 0 0 .4rem;">remove_red_eye</i>
                                </span>
                            @endif
                            @if ($message->authority(false) && $message->spam > 0)
                                <span class="badge d-flex justify-content-end red-text">
                                    <span class="align-self-center">{{ $message->spam }}</span>
                                    <i class="material-icons align-self-center" style="margin: 0 0 0 .4rem;">bug_report</i>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($message->reply_id)
                    <div data-id="message-{{ $message->id }}" class="card-content">
                        <blockquote>
                            <div class="markdown">{!! $message->reply->markdown() !!}</div>
                            <cite class="d-block right-align">
                                <time class="timeago grey-text" data-time="{{ $message->reply->created_at }}">{{ date('d.m.Y H:i', strtotime($message->reply->created_at)) }}</time>
                                <a href="{{ route('user.profile', $message->reply->user_id) }}">{{ $message->reply->user->name }}</a>
                            </cite>
                        </blockquote>
                    </div>
                @endif
                <div data-id="message-{{ $message->id }}" class="card-content">
                    <div class="markdown" data-original-source="{{ $message->id }}"> 
                        {!! $message->markdown() !!}
                    </div>

                    @if ($message->updated_user_id)
                        <p class="mt-1">
                            <i class="grey-text">
                                <time class="timeago" data-time="{{ $message->updated_at }}">{{ date('d.m.Y H:i', strtotime($message->updated_at)) }}</time>
                                <a href="{{ route('user.profile', $message->updated_user_id) }}">{{ $message->updatedUser->name }}</a> tarafından güncellendi.
                            </i>
                        </p>
                    @endif

                    <div class="d-flex mt-1">
                        <button class="btn-flat waves-effect green-text json" data-href="{{ route('forum.message.vote') }}" data-method="post" data-callback="__vote" data-id="{{ $message->id }}" data-type="pos">
                            <i class="material-icons">exposure_plus_1</i>
                        </button>
                        <button class="btn-flat waves-effect red-text json" data-href="{{ route('forum.message.vote') }}" data-method="post" data-callback="__vote" data-id="{{ $message->id }}" data-type="neg">
                            <i class="material-icons">exposure_neg_1</i>
                        </button>
                        <span class="badge grey-text align-self-center" data-name="vote">{{ $message->vote }}</span>
                    </div>
                </div>
                @php
                    $last_user_id = $message->user_id;
                @endphp
            @empty
                <div class="card-content">
                    @component('components.nothing')
                        @slot('text', 'Bu sayfa da gösterilecek içerik bulunamadı.')
                        @slot('text_class', 'grey-text')
                    @endcomponent
                </div>
            @endforelse
        </div>
    </div>

    {!! $messages->links('vendor.pagination.materializecss') !!}

    @auth
        <form
            id="message-form"
            class="json {{ $thread->closed ? 'hide' : '' }}"
            action="{{ route('forum.reply.submit') }}"
            data-thread_id="{{ $thread->id }}"
            data-section="reply"
            data-method="post"
            data-callback="__submit">
            <input type="hidden" name="reply_id" id="reply_id" value="{{ $thread->id }}" />
            <div class="card">
                <div class="card-content hide" data-section="quote">
                    <div class="right-align">
                        <a href="#" class="red-text" data-name="close">
                            <i class="material-icons">close</i>
                        </a>
                    </div>
                    <blockquote>
                        <div class="markdown"></div>
                    </blockquote>
                </div>
                <div class="card-content">
                    <span class="card-title">Cevapla</span>
                </div>
                <div class="card-tabs">
                    <ul class="tabs">
                        <li class="tab">
                            <a href="#textarea" class="waves-effect active">Cevapla</a>
                        </li>
                        <li class="tab">
                            <a href="#preview" class="waves-effect">Ön İzle</a>
                        </li>
                    </ul>
                </div>
                <div class="card-content textarea-content" id="textarea">
                    <div class="input-field">
                        <textarea id="body" name="body" class="materialize-textarea validate" data-length="5000"></textarea>
                        <label for="body">Cevap İçeriği</label>
                        <small class="grey-text">Bu alanda <a href="https://guides.github.com/features/mastering-markdown/" target="_blank">Markdown</a> kullanabilirsiniz.</small>
                        <span class="helper-text"></span>
                    </div>
                </div>
                <div
                    class="card-content"
                    id="preview"
                    data-href="{{ route('markdown.preview') }}"
                    data-method="post"
                    data-include="body"
                    data-callback="__preview"
                    style="display: none;">
                    <div class="markdown"></div>
                </div>
                <div class="card-action right-align">
                    <button type="submit" class="btn-flat waves-effect">Gönder</button>
                </div>
            </div>
        </form>
    @endauth
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/highlight.min.js?v='.config('system.version')) }}"></script>
@endpush
