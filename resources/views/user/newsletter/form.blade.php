@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin',
        ],
        [
            'text' => 'E-posta Bülteni',
            'link' => route('admin.user.newsletter')
        ],
        [
            'text' => $nlet ? $nlet->subject : 'Bülten Oluştur'
        ]
    ]
])

@section('wildcard')
    <div class="card wild-background">
        <div class="card-image">
            <a href="{{ route('admin.user.newsletter') }}" class="btn-floating btn-large halfway-fab waves-effect teal" data-tooltip="Vazgeç" data-position="left">
                <i class="material-icons">close</i>
            </a>
        </div>
        <div class="container">
            <span class="wildcard-title white-text">{{ $nlet ? $nlet->subject : 'Bülten Oluştur' }}</span>
        </div>
    </div>
@endsection

@section('content')
    <form id="thread-form" class="json" action="{{ route('admin.user.newsletter.form') }}" data-method="post" data-callback="__submit">
        @if ($nlet)
            <input type="hidden" name="id" id="id" value="{{ $nlet->id }}" />
        @endif
        <div class="card">
            <div class="card-content">
                <div class="input-field">
                    <input id="subject" name="subject" type="text" class="validate" data-length="64" value="{{ @$nlet->subject }}" />
                    <label for="subject">Konu Başlığı</label>
                </div>
            </div>
            <div class="card-tabs">
                <ul class="tabs">
                    <li class="tab">
                        <a href="#textarea" class="active">Konu İçeriği</a>
                    </li>
                    <li class="tab">
                        <a href="#preview">Önizleme</a>
                    </li>
                </ul>
            </div>
            <div class="card-content textarea-content" id="textarea">
                <div class="input-field">
                    <textarea id="body" name="body" class="materialize-textarea validate" data-length="5000">{{ @$nlet->body }}</textarea>
                    <label for="body">Konu İçeriği</label>
                    <div class="helper-text"></div>
                    <small class="grey-text">Bu alanda <a href="https://guides.github.com/features/mastering-markdown/" target="_blank">Markdown</a> kullanabilirsiniz.</small>
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
            <hr />
            <div class="card-content">
                <div class="d-flex flex-wrap">
                    <div class="p-1" style="min-width: 50%;">
                        <div class="input-field">
                            <textarea name="mail_list" id="mail_list" data-length="10000" class="materialize-textarea validate max-height"></textarea>
                            <label for="mail_list">Alıcı Listesi</label>
                            <span class="helper-text">Bültenin gönderileceği kullanıcıların e-posta adreslerini girin.</span>
                        </div>
                        <a href="#" class="btn-flat waves-effect json" data-href="{{ route('admin.user.newsletter.users') }}" data-method="post" data-callback="__load_users">Kullanıcıları Getir</a>
                    </div>
                    <div class="p-1" style="min-width: 50%;">
                        <div class="input-field">
                            <textarea readonly name="sent_list" id="sent_list" placeholder="Gönderilen Listesi" data-length="10000" class="materialize-textarea validate max-height"></textarea>
                            <label for="sent_list">Gönderilen Listesi</label>
                            <span class="helper-text">Bülteni alan kullanıcıların listesi.</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap">
                    <div class="p-1" style="min-width: 50%;">
                        <div class="input-field">
                            <input type="text" name="send_date" id="send_date" class="validate datepicker" />
                            <label for="send_date">Gönderileceği Tarih</label>
                        </div>
                    </div>
                    <div class="p-1" style="min-width: 50%;">
                        <div class="input-field">
                            <input type="text" name="send_time" id="send_time" class="validate timepicker" />
                            <label for="send_time">Gönderileceği Saat</label>
                        </div>
                    </div>
                </div>
                <div class="collection">
                    <label class="collection-item waves-effect d-block">
                        <input name="status" id="status" value="on" type="checkbox" />
                        <span>İşlem Kuyruğuna Al</span>
                    </label>
                </div>
            </div>
            <div class="card-action right-align">
                <button type="submit" class="btn-flat waves-effect">{{ $nlet ? 'Güncelle' : 'Oluştur' }}</button>
            </div>
        </div>
    </form>
@endsection

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/highlight.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/highlight.min.js?v='.config('system.version')) }}"></script>
@endpush

@push('local.scripts')
    function __load_users(__, obj)
    {
        if (obj.status == 'ok')
        {
            var textarea = $('textarea[name=mail_list]');

                textarea.val(obj.data.hits.join('\n'))

            M.textareaAutoResize(textarea)

            M.toast({
                html: obj.data.hits.length + ' e-posta yüklendi.'
            })
        }
    }

    $('.datepicker').datepicker({
        firstDay: 0,
        format: 'yyyy-mm-dd',
        i18n: date.i18n
    })

    $('.timepicker').timepicker({
        format: 'hh:MM',
        twelveHour: false,
        i18n: date.i18n
    })

    function __submit(__, obj)
    {
        if (obj.status == 'ok')
        {
            //
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

        $('input[name=subject], textarea[name=body], textarea[name=mail_list]').characterCounter()
    })

    $(document).on('keyup', 'textarea[name=mail_list]', function() {
        console.log('test')
    })
@endpush
