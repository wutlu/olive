@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => '...',
            'link' => route('forum.index')
        ],
        [
            'text' => '...',
            'link' => route('forum.category', $thread->category->slug)
        ],
        [
            'text' => $thread->subject,
            'link' => $thread->route()
        ],
        [
            'text' => @$edit ? 'Cevap Güncelle' : 'Cevap Ekle'
        ]
    ]
])

@section('wildcard')
    <div class="card wild-background">
        <div class="card-image">
            <a href="{{ $thread->route() }}" class="btn-floating btn-large halfway-fab waves-effect red darken-2" data-tooltip="Vazgeç" data-position="left">
                <i class="material-icons">close</i>
            </a>
        </div>
        <div class="container">
            <span class="wildcard-title white-text">{{ @$edit ? 'Cevap Güncelle' : 'Cevap Ekle' }}</span>
        </div>
    </div>
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

@section('content')
    <form id="message-form" class="json" action="{{ route('forum.reply.submit') }}" data-method="post" data-callback="__submit">
        <input type="hidden" name="reply_id" id="reply_id" value="{{ $reply->id }}" />
        @isset ($edit)
            <input type="hidden" name="edit" id="edit" value="on" />
        @endif
        <div class="card">
            @if ($thread->closed)
                <div class="card-content">
                    @component('components.nothing')
                        @slot('cloud', 'cloud_off')
                        @slot('sun', 'sentiment_very_dissatisfied')
                        @slot('text', 'İlgili konu kapandığından, cevap ekleyemezsiniz.')
                    @endcomponent
                </div>
            @endif
            @unless (@$edit)
                <div class="card-content orange lighten-5">
                    <div class="markdown">
                        {!! $reply->markdown() !!}
                    </div>
                </div>
            @endif
            <div class="card-tabs teal accent-3">
                <ul class="tabs tabs-transparent">
                    <li class="tab">
                        <a href="#textarea" class="active">Cevap İçeriği</a>
                    </li>
                    <li class="tab">
                        <a href="#preview">Önizleme</a>
                    </li>
                </ul>
            </div>
            <div class="card-content textarea-content" id="textarea">
                <div class="input-field">
                    <textarea id="body" name="body" class="materialize-textarea validate" data-length="5000">{{ @$edit ? $reply->body : '' }}</textarea>
                    <label for="body">Cevap İçeriği</label>
                    <div class="helper-text">Bu alanda <a href="https://guides.github.com/features/mastering-markdown/" target="_blank">Markdown</a> kullanabilirsiniz.</div>
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
            <div class="card-action right-align teal accent-3">
                <button type="submit" class="btn-flat waves-effect">{{ @$edit ? 'Güncelle' : 'Ekle' }}</button>
            </div>
        </div>
    </form>
@endsection

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/highlight.min.css?v='.config('app.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/highlight.min.js?v='.config('app.version')) }}"></script>
@endpush

@push('local.scripts')
    function __submit(__, obj)
    {
        if (obj.status == 'ok')
        {
            @isset ($edit)
                M.toast({ html: 'Cevap Güncellendi', classes: 'green darken-2' })
            @else
                window.location = obj.data.route;
            @endisset
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

        $('input[type=text], textarea').characterCounter()
    })
@endpush
