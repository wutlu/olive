@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => implode(' ', [ config('app.name'), 'Forumları' ]),
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

@section('wildcard')
    <div class="card wild-background">
        @auth
            <div class="card-image">
                <a
                    href="#"
                    class="btn-floating btn-large halfway-fab waves-effect teal {{ $thread->closed ? 'hide' : '' }}"
                    data-id="{{ $thread->id }}"
                    data-button="reply"
                    data-tooltip="Cevapla"
                    data-position="left">
                    <i class="material-icons">reply</i>
                </a>
            </div>
        @else
            <div class="card-image">
                <a href="{{ route('user.login') }}" class="btn-floating btn-large halfway-fab waves-effect teal" data-tooltip="Giriş Yap" data-position="left">
                    <i class="material-icons">person</i>
                </a>
            </div>
        @endauth
        <div class="container">
            <span class="wildcard-title white-text">{{ $thread->subject }}</span>
        </div>
    </div>
@endsection

@push('local.scripts')
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
                                'class': 'waves-effect btn-flat cyan-text json',
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

                        M.toast({ html: 'Konu Sabitlendi', classes: 'red darken-2' })
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
                                    'class': 'collection',
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
                                'class': 'waves-effect btn-flat cyan-text',
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
                            'class': 'waves-effect btn-flat cyan-text json',
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
    @endauth

    function __vote(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('[data-id=message-' + obj.data.id + ']').find('[data-name=vote]').html(obj.data.vote)
        }
    }

    $('[data-button=reply]').click(function() {
        var __ = $(this);

        scrollTo({
            'target': '[data-section=reply]',
            'tolerance': '-96px'
        })

        $('input[name=reply_id]').val(__.data('id'))

        if (__.data('quote'))
        {
            var markdown = eval(element('[data-quote-source=' + __.data('quote') + ']'));

            $('[data-section=quote]').removeClass('hide').find('.markdown').html(markdown)
        }
        else
        {
            $('[data-section=quote]').addClass('hide')
        }
    })
@endpush

@section('content')
    @php
        $last_user_id = 0;
    @endphp

    <div data-section="messages">
        <div class="card">
            @forelse ($messages as $message)
                @auth
                    <ul id="thread-menu-{{ $message->id }}" class="dropdown-content">
                        @if ($message->message_id)
                            <li>
                                <a href="#" class="waves-effect">Güncelle</a>
                            </li>
                        @else
                            @if ($message->authority())
                               <li>
                                   <a href="{{ route('forum.thread.form', $thread->id) }}" class="waves-effect">Güncelle</a>
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

                            <li data-button="reply" data-quote="{{ $message->id }}" data-id="{{ $message->id }}" class="{{ $thread->closed ? 'hide' : '' }}">
                                <a href="#" class="waves-effect">Cevapla</a>
                            </li>

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
                    <div class="card">
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
                                <a class="card-title card-title-small align-self-center mb-0" href="{{ route('user.profile', $message->user_id) }}">{{ $message->user->name }}</a>
                            @endif
                            <time class="timeago grey-text text-darken-2" data-time="{{ $message->created_at }}">{{ date('d.m.Y H:i', strtotime($message->created_at)) }}</time>
                        </div>
                        <div class="align-self-center ml-auto d-flex flex-column">
                            @auth
                                <a style="margin-bottom: .4rem;" class="btn-floating btn-flat waves-effect ml-auto dropdown-trigger" data-target="thread-menu-{{ $message->id }}" data-align="right">
                                    <i class="material-icons">more_vert</i>
                                </a>
                            @endauth
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
                        </div>
                    </div>
                </div>

                @if ($message->reply_id && $message->reply_id != $thread->id)
                    <div class="card-content">
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
                    <div class="markdown" data-quote-source="{{ $message->id }}"> 
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
                        <div class="helper-text"></div>
                        <small class="grey-text">Bu alanda <a href="https://guides.github.com/features/mastering-markdown/" target="_blank">Markdown</a> kullanabilirsiniz.</small>
                    </div>
                </div>
                <div
                    class="card-content"
                    id="preview"
                    data-href="{{ route('forum.message.preview') }}"
                    data-method="post"
                    data-include="body"
                    data-callback="__preview"
                    style="display: none;">
                    <div class="markdown"></div>
                </div>
                <div class="card-action right-align">
                    <button type="submit" class="btn-flat waves-effect">
                        <i class="material-icons">send</i>
                    </button>
                </div>
            </div>
        </form>
    @endauth
@endsection

@push('local.styles')
    textarea {
        border-width: 0 !important;
        box-shadow: none !important;
        margin-top: 0;
        margin-bottom: 0;
        min-height: 200px !important;
    }

    .textarea-content {
        padding: 12px 24px !important;
    }
@endpush

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/highlight.min.css?v='.config('app.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/highlight.min.js?v='.config('app.version')) }}"></script>
@endpush

@push('external.include.header')
    <meta name="description" content="{{ str_limit($thread->body, 255) }}" />

    <meta property="og:title" content="{{ $thread->subject }}">
    <meta property="og:description" content="{{ str_limit($thread->body, 255) }}" />
    <meta property="og:type" content="article" />
    <meta property="og:url" content="{{ url()->full() }}" />
    <meta property="og:image" content="{{ asset('img/olive-twitter-card.png?v='.config('app.version')) }}" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:site" content="{{ url()->full() }}" />
    <meta name="twitter:title" content="{{ $thread->subject }}" />
    <meta name="twitter:description" content="{{ str_limit($thread->body, 255) }}" />
    <meta name="twitter:image" content="{{ asset('img/olive-twitter-card.png?v='.config('app.version')) }}" />

    <link rel="stylesheet" href="{{ asset('css/highlight.min.css?v='.config('app.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/highlight.min.js?v='.config('app.version')) }}"></script>
    <script src="{{ asset('js/jquery.ui.min.js?v='.config('app.version')) }}"></script>
@endpush

@push('local.scripts')
    @auth
        $(document).on('click', '[data-section=quote] [data-name=close]', function() {
            var __ = $(this);
                __.closest('.card-content').addClass('hide')
            $('input[name=reply_id]').val('{{ $thread->id }}')
        })
    @endauth

    var hash = window.location.hash.substr(1);

    if (hash)
    {
        $(window).on('load', function() {
            scrollTo({
                'target': '#' + hash,
                'tolerance': '-72px'
            })

            $('[data-id=' + hash + ']').hide().show('highlight', {}, 4000)
        })
    }

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

    $('code').each(function(i, block) {
        hljs.highlightBlock(block);
    })
@endpush
