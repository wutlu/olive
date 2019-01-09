@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin',
        ],
        [
            'text' => 'E-posta Bülteni',
            'link' => route('admin.newsletter')
        ],
        [
            'text' => $newsletter ? $newsletter->subject : 'Bülten Oluştur'
        ]
    ]
])

@section('wildcard')
    <div class="card wild-background">
        <div class="card-image">
            <a href="{{ route('admin.newsletter') }}" class="btn-floating btn-large halfway-fab waves-effect teal" data-tooltip="Vazgeç" data-position="left">
                <i class="material-icons">close</i>
            </a>
        </div>
        <div class="container">
            <span class="wildcard-title white-text">{{ $newsletter ? $newsletter->subject : 'Bülten Oluştur' }}</span>
        </div>
    </div>
@endsection

@section('content')
    <form
        id="newsletter-form"
        class="json"
        action="{{ route('admin.newsletter.form.save') }}"
        method="post"
        data-callback="__submit">
        @if ($newsletter)
            <input name="id" id="id" value="{{ $newsletter->id }}" type="hidden" />
        @endif
        <div class="card">
            <div class="card-content">
                <div class="d-flex flex-wrap">
                    <div style="min-width: 50%;">
                        <div class="input-field">
                            <input type="text" name="send_date" id="send_date" class="validate datepicker" value="{{ @$newsletter->send_date ? date('Y-m-d', strtotime($newsletter->send_date)) : '' }}" />
                            <label for="send_date">Gönderileceği Tarih</label>
                            <span class="helper-text"></span>
                        </div>
                    </div>
                    <div style="min-width: 50%;">
                        <div class="input-field">
                            <input type="text" name="send_time" id="send_time" class="validate timepicker" value="{{ @$newsletter->send_date ? date('H:i', strtotime($newsletter->send_date)) : '' }}" />
                            <label for="send_time">Gönderileceği Saat</label>
                            <span class="helper-text"></span>
                        </div>
                    </div>
                </div>
                <div class="input-field">
                    <input id="subject" name="subject" type="text" class="validate" data-length="64" value="{{ @$newsletter->subject }}" />
                    <label for="subject">Konu Başlığı</label>
                    <span class="helper-text"></span>
                </div>
            </div>
            <div class="card-tabs teal">
                <ul class="tabs tabs-transparent">
                    <li class="tab">
                        <a href="#textarea" class="active">Konu İçeriği</a>
                    </li>
                    <li class="tab">
                        <a href="#preview">Önizleme</a>
                    </li>
                    <li class="tab">
                        <a href="#users">Alıcılar (<span data-name="user-count">0</span>)</a>
                    </li>
                </ul>
            </div>
            <div class="card-content textarea-content" id="textarea">
                <div class="input-field">
                    <textarea id="body" name="body" class="materialize-textarea validate" data-length="10000">{{ @$newsletter->body }}</textarea>
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
            <div class="card-content" id="users" style="display: none;">
                <div class="input-field">
                    <textarea name="email_list" id="email_list" data-length="10000" class="materialize-textarea validate max-height">{{ @$newsletter->email_list }}</textarea>
                    <label for="email_list">Alıcı Listesi</label>
                    <span class="helper-text">Bültenin gönderileceği kullanıcıların e-posta adreslerini girin.</span>
                </div>
                <a href="#" class="btn waves-effect" data-trigger="call-users">Kayıtlı Kullanıcıları Getir</a>
            </div>
            <div class="card-action red {{ @$newsletter->status == 'process' ? '' : 'hide' }}" data-name="process">
                <span class="white-text">
                    <span data-name="sent-line">0 / 0</span>
                    İşleniyor...
                </span>
            </div>

            <div class="card-action right-align {{ @$newsletter->status == 'process' ? 'hide' : '' }}" data-name="action">
                @if ($newsletter)
                    <a
                        href="#"
                        class="btn-flat red-text waves-effect"
                        data-trigger="delete">Sil</a>
                @endif
                <label class="btn-flat waves-effect" data-name="status-checkbox">
                    <input name="status" id="status" value="on" type="checkbox" {{ @$newsletter->status == 'triggered' ? 'checked' : '' }} />
                    <span>Kuyruğa Al</span>
                </label>
                <button type="submit" class="btn-flat waves-effect">{{ $newsletter ? 'Güncelle' : 'Oluştur' }}</button>
            </div>
        </div>
    </form>
    @if ($newsletter)
        <div
            id="load-status"
            class="load"
            data-href="{{ route('admin.newsletter.status') }}"
            data-id="{{ $newsletter->id }}"
            data-method="post"
            data-callback="__status">
        </div>
    @endif
@endsection

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/highlight.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/highlight.min.js?v='.config('system.version')) }}"></script>
@endpush

@push('local.scripts')
    var statusTimer;

    function __status(__, obj)
    {
        if (obj.status == 'ok')
        {
            if (obj.data.status == 'process')
            {
                $('[data-name=action]').addClass('hide')
                $('[data-name=process]').removeClass('hide')
                                        .find('[data-name=sent-line]')
                                        .html(obj.data.sent_line + ' / ' + obj.data.total_line)
            }
            else
            {
                $('[data-name=action]').removeClass('hide')
                $('[data-name=process]').addClass('hide')
            }

            window.clearTimeout(statusTimer)

            statusTimer = window.setTimeout(function() {
                vzAjax($('#load-status'))
            }, obj.data.status == 'process' ? 1000 : 5000)
        }
    }

    function user_count()
    {
        var textarea = $('textarea[name=email_list]');

        $('[data-name=user-count]').html(textarea.val().length ? textarea.val().split(/\r\n|\r|\n/).length : 0)
    }

    $(document).on('click', '[data-trigger=call-users]', function() {
        return modal({
            'id': 'alert',
            'body': 'E-posta listesi yenilenecek. Devam etmek istiyor musunuz?',
            'size': 'modal-small',
            'title': 'Uyarı',
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect grey-text btn-flat',
                    'html': buttons.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<a />', {
                    'href': '#',
                    'class': 'waves-effect btn-flat red-text json',
                    'html': buttons.ok,
                    'data-href': '{{ route('admin.newsletter.users') }}',
                    'data-method': 'post',
                    'data-callback': '__load_users'
                })
            ],
            'options': {}
        })
    })

    @if ($newsletter)
        $(document).on('click', '[data-trigger=delete]', function() {
            return modal({
                'id': 'delete',
                'body': 'Bülten silinecek. Devam etmek istiyor musunuz?',
                'size': 'modal-small',
                'title': 'Sil',
                'footer': [
                    $('<a />', {
                        'href': '#',
                        'class': 'modal-close waves-effect grey-text btn-flat',
                        'html': buttons.cancel
                    }),
                    $('<span />', {
                        'html': ' '
                    }),
                    $('<a />', {
                        'href': '#',
                        'class': 'waves-effect btn-flat red-text json',
                        'html': buttons.ok,
                        'data-href': '{{ route('admin.newsletter.delete') }}',
                        'data-id': '{{ $newsletter->id }}',
                        'data-method': 'delete',
                        'data-callback': '__delete'
                    })
                ],
                'options': {}
            })
        })

        function __delete(__, obj)
        {
            if (obj.status == 'ok')
            {
                location.href = '{{ route('admin.newsletter') }}';
            }
        }
    @endif

    function __load_users(__, obj)
    {
        if (obj.status == 'ok')
        {
            var textarea = $('textarea[name=email_list]');

                textarea.val(obj.data.hits.join('\n'))

            M.textareaAutoResize(textarea)

            if (obj.data.hits.length)
            {
                M.toast({
                    html: obj.data.hits.length + ' e-posta yüklendi.'
                })
            }
            else
            {
                M.toast({
                    html: 'E-posta onaylı kullanıcı bulunamadı.',
                    classes: 'red'
                })
            }

            user_count()

            $('#modal-alert').modal('close')
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
            if (obj.data.status == 'created')
            {
                location.href = '{{ route('admin.newsletter') }}'
            }
            else if (obj.data.status == 'updated')
            {
                M.toast({
                    html: 'Bülten Güncellendi',
                    classes: 'green'
                })
            }
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

        user_count()

        $('input[name=subject], textarea[name=body], textarea[name=email_list]').characterCounter()
    })

    $(document).on('keyup', 'textarea[name=email_list]', function() {
        user_count()
    })
@endpush
