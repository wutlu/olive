@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Bot Yönetimi',
            'link' => route('crawlers')
        ],
        [
            'text' => 'Blog & Forum Botları',
            'link' => route('crawlers.blog.list')
        ],
        [
            'text' => '🐞 '.$crawler->name
        ]
    ],
    'footer_hide' => true
])

@push('local.scripts')
    $('[data-length]').characterCounter()

    function __status(__, obj)
    {
        if (obj.status == 'ok')
        {
            var __ = $('[data-trigger=status]');

                __.removeClass('waves-red red-text waves-green green-text')

            if (obj.data.status)
            {
                __.addClass('waves-green green-text').html('AKTİF')

                $('[data-name=error-count]').html('0 hata')
            }
            else
            {
                __.addClass('waves-red red-text').html('PASİF')
            }

            $('#modal-status').modal('close')
        }
    }

    $(document).on('click', '[data-trigger=status]', function() {
        return modal({
            'id': 'status',
            'body': 'Bot durumunu değiştirmek istediğinizden emin misiniz?',
            'size': 'modal-small',
            'title': 'Durum',
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
                    'data-href': '{{ route('crawlers.blog.bot.status') }}',
                    'data-id': '{{ $crawler->id }}',
                    'data-method': 'post',
                    'data-callback': '__status'
                })
            ]
        })
    })

    var statTimer;

    function __stats(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('[data-name=control-date]').attr('data-time', obj.data.crawler.control_date)
            $('[data-name=error-count]').html(obj.data.crawler.error_count + ' hata')

            try { $('[data-name=total-docs-success]').html(number_format(obj.data.count.success.data.count)) }
            catch (err) { $('[data-name=total-docs-success]').html('es {err}') }
            try { $('[data-name=total-docs-failed]').html(number_format(obj.data.count.failed.data.count)) }
            catch (err) { $('[data-name=total-docs-failed]').html('es {err}') }
            try { $('[data-name=total-docs-buffer]').html(number_format(obj.data.count.buffer.data.count)) }
            catch (err) { $('[data-name=total-docs-buffer]').html('es {err}') }

            window.clearTimeout(statTimer)

            statTimer = setTimeout(function() {
                vzAjax($('#stats'))
            }, 10000)
        }
    }

    function __connection_failed(__)
    {
        $('[data-elasticsearch]').html('ES Bağlantı Hatası')
    }

    function __clear(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'Tüm başarısız içerikler silinmek üzere planlandı.', classes: 'green darken-2' })

            $('#modal-trigger').modal('close')
        } 
    }

    $(document).on('click', '[data-trigger=trigger]', function() {
        var __ = $(this);

        return modal({
            'id': 'trigger',
            'body': __.data('message'),
            'size': 'modal-small',
            'title': 'Uyarı',
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
                    'data-href': __.data('href'),
                    'data-method': 'post',
                    'data-callback': __.data('callback')
                })
            ]
        });
    })
@endpush

@section('content')
    <form
        method="patch"
        action="{{ route('crawlers.blog.bot') }}"
        class="json"
        id="details-form"
        data-callback="__test">
        <input type="hidden" value="{{ $crawler->id }}" name="id" id="id" />
        <div class="card">
            <div class="card-content">
                <span class="card-title" data-name="crawler-title">{{ $crawler->name }}</span>
                <span class="grey-text text-darken-2">
                    <span data-name="error-count">-</span> / <time class="timeago" data-name="control-date">-</time>
                </span>
            </div>
            <div
                id="stats"
                class="item-group grey lighten-5 p-2 load"
                data-method="post"
                data-timeout="4000"
                data-href="{{ route('crawlers.blog.bot.statistics', $crawler->id) }}"
                data-callback="__stats"
                data-error-callback="__connection_failed">
                <div class="item align-self-center">
                    <button type="submit" class="btn-flat waves-effect cyan-text d-flex">
                        <i class="material-icons mr-1">done_all</i> TEST
                    </button>
                </div>

                <div class="item align-self-center">
                    <a href="#" data-trigger="status" class="btn-flat waves-effect waves-{{ $crawler->status ? 'green green' : 'red red' }}-text">{{ $crawler->status ? 'AKTİF' : 'PASİF' }}</a>
                </div>

                <div class="item">
                    <small class="grey-text">KUYRUK</small>
                    <div class="d-block" data-elasticsearch data-name="total-docs-buffer">-</div>
                </div>

                <div class="item">
                    <small class="grey-text">BAŞARILI / <a href="#"
                                                           data-message="Başarısız içerikler silinecek?"
                                                           data-trigger="trigger"
                                                           data-href="{{ route('crawlers.blog.bot.clear', $crawler->id) }}"
                                                           data-callback="__clear">BAŞARISIZ</a></small>
                    <div class="d-flex">
                        <span data-elasticsearch data-name="total-docs-success">-</span>
                        <span> / </span>
                        <span data-elasticsearch data-name="total-docs-failed">-</span>
                    </div>
                </div>
            </div>
            @if (!$crawler->status && $crawler->off_reason)
                <div class="card-content red white-text">
                    <small class="black-text">Kapanma Nedeni</small>
                    <p class="d-block">{{ $crawler->off_reason }}</p>
                </div>
            @endif
            <div class="card-content">
                <div class="collection">
                    <div class="collection-item">
                        @push('local.scripts')
                            $(document).on('keydown keyup', 'input[name=name]', function() {
                                var __ = $(this);

                                $('[data-name=crawler-title], [data-name=breadcrumb]').html(__.val())
                            })
                        @endpush
                        <div class="input-field">
                            <input name="name" id="name" value="{{ $crawler->name }}" type="text" class="validate" data-length="24" />
                            <label for="name">Bot Adı</label>
                            <small class="helper-text">Veri toplanacak sitenin adı.</small>
                        </div>
                    </div>
                    <div class="collection-item">
                        <div class="d-flex flex-wrap">
                            <div style="min-width: 50%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="site" id="site" value="{{ $crawler->site }}" type="text" class="validate" data-length="255" />
                                    <label for="site">Ana Sayfa</label>
                                    <small class="helper-text">Veri toplanacak sitenin kök http(s) adresi.</small>
                                </div>
                            </div>
                            <div style="min-width: 50%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="base" id="base" value="{{ $crawler->base }}" type="text" class="validate" data-length="255" />
                                    <label for="base">Temel Dizin</label>
                                    <small class="helper-text">Ana Sayfa alt segmentten oluşuyorsa belirtin.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="collection-item">
                        <label>
                            <input
                                name="standard"
                                id="standard"
                                value="on"
                                type="checkbox"
                                {{ $crawler->standard ? 'checked' : '' }} />
                            <span>RSS ile Standart Toplama</span>
                        </label>
                    </div>

                    @push('local.scripts')
                        $(document).on('click', '[data-id=match]', function() {
                            var __ = $(this);
                            var url_pattern = $('input[name=url_pattern]');
                                url_pattern.val(url_pattern.val() + __.data('pattern'))
                                url_pattern.focus()

                                M.updateTextFields()
                        })
                    @endpush
                    <div class="collection-item green lighten-5 z-depth-1 {{ $crawler->standard ? 'hide' : '' }}" data-name="patterns">
                        <div class="input-field">
                            <input name="url_pattern" id="url_pattern" value="{{ $crawler->url_pattern }}" type="text" class="validate" data-length="255" />
                            <label for="url_pattern">Makale URL Deseni</label>
                            <small class="helper-text">Kaynak içerik adreslerinin <strong>REGEX</strong> deseni.</small>
                        </div>
                        @include('crawlers._inc.regex')
                    </div>
                    <div class="collection-item {{ $crawler->standard ? 'hide' : '' }}" data-name="patterns">
                        <div class="d-flex flex-wrap">
                            <div style="min-width: 50%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="selector_title" id="selector_title" value="{{ $crawler->selector_title }}" type="text" class="validate" data-length="255" />
                                    <label for="selector_title">Makale Başlık Seçicisi</label>
                                    <small class="helper-text">Kaynak içerik başlığının CSS seçicisi.</small>
                                </div>
                            </div>
                            <div style="min-width: 50%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="selector_description" id="selector_description" value="{{ $crawler->selector_description }}" type="text" class="validate" data-length="255" />
                                    <label for="selector_description">Makale Açıklama Seçicisi</label>
                                    <small class="helper-text">Kaynak içerik açıklamasının CSS seçicisi.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="collection-item">
                        <div class="d-flex flex-wrap">
                            <div style="width: 50%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="off_limit" id="off_limit" value="{{ $crawler->off_limit }}" type="number" class="validate" max="255" min="10" />
                                    <label for="off_limit">Kapatma Limiti</label>
                                    <small class="helper-text">
                                        Belirtilen değer kadar hata alındığı takdirde; hata logu girilir ve bot devre dışı bırakılır.
                                    </small>
                                </div>
                            </div>
                            <div style="width: 50%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="control_interval" id="control_interval" value="{{ $crawler->control_interval }}" type="number" class="validate" max="1440" min="1" />
                                    <label for="control_interval">Kontrol Aralığı (Dakika)</label>
                                    <small class="helper-text" data-name="minute">Girilen değer aralığında içerik kontrolü yapılır. (Bu değeri sistem optimum olarak güncelleyecktir.)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <label class="collection-item waves-effect d-block">
                        <input
                            name="proxy"
                            id="proxy"
                            value="on"
                            type="checkbox"
                            {{ $crawler->proxy ? 'checked' : '' }} />
                        <span>Proxy Kullan</span>
                    </label>
                    <label class="collection-item waves-effect d-block">
                        <input
                            name="cookie"
                            id="cookie"
                            value="on"
                            type="checkbox"
                            {{ $crawler->cookie ? 'checked' : '' }} />
                        <span>Çerezleri Kabul Et</span>
                    </label>
                </div>
            </div>
            <div class="card-content yellow lighten-4">Bu aşamada girilen değerler test edilir. Test sonucu olumlu olmadığı sürece değerler kaydedilmeyecektir.</div>
            <div class="card-action right-align">
                <a href="#" class="btn-flat waves-effect red-text" data-trigger="delete">
                    <i class="material-icons">close</i>
                </a>
                <button type="submit" class="btn-flat waves-effect">
                    <i class="material-icons">done_all</i>
                </button>

                <div class="d-flex justify-content-end" style="margin: 1rem 0 0;">
                    <div class="input-field" style="max-width: 124px;">
                        <input name="test_count" id="test_count" value="1" type="number" class="validate" max="100" min="1" />
                        <label for="test_count">Test Sayısı</label>
                        <small class="helper-text">Girilen değer kadar içerik üzerinde test yapılır.</small>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('local.scripts')
    $(document).on('change', 'input[name=standard]', function() {
        var patterns = $('[data-name=patterns]');

        if ($('#standard:checkbox:checked').length > 0)
        {
            patterns.addClass('hide')
        }
        else
        {
            patterns.removeClass('hide')
        }
    })

    $(document).on('click', '[data-trigger=delete]', function() {
        return modal({
            'id': 'status',
            'body': 'Bot silinecek?',
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
                    'data-href': '{{ route('crawlers.blog.bot') }}',
                    'data-id': '{{ $crawler->id }}',
                    'data-method': 'delete',
                    'data-callback': '__delete'
                })
            ]
        })
    })

    function __delete(__, obj)
    {
        if (obj.status == 'ok')
        {
            location.href = '{{ route('crawlers.blog.list') }}';
        }
    }

    function __test(__, obj)
    {
        var mdl = modal({
            'id': 'error',
            'body': '',
            'size': 'modal-large',
            'title': obj.status == 'ok' ? 'Test Başarılı!' : 'Test Başarısız!',
            'options': {},
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect btn-flat',
                    'html': buttons.ok
                })
            ]
        });

        mdl.find('modal-content').removeClass('red green')
        mdl.find('modal-content').addClass(obj.status == 'ok' ? 'green' : 'red')

        var textarea = $('<textarea />', {
            'style': 'border-width:0; resize: none; min-height: 200px; background-color: transparent;'
        });

        if (obj.status == 'err')
        {
            $.each(obj.error_reasons, function(key, reason) {
                textarea.val(textarea.val() + '--------------------------------\n');
                textarea.val(textarea.val() + reason + '\n');
                textarea.val(textarea.val() + '--------------------------------\n\n');
            })
        }

        if (obj.items)
        {
            $.each(obj.items, function(key, o) {
                textarea.val(textarea.val() + '--------------------------------\n');
                textarea.val(textarea.val() + o.page + '\n');

                if (o.data)
                {
                    textarea.val(textarea.val() + '- ' + o.data.title + '\n');
                    textarea.val(textarea.val() + '- ' + o.data.description + '\n');
                    textarea.val(textarea.val() + '- ' + o.data.created_at + '\n');
                }
                else
                {
                    textarea.val(textarea.val() + '- ' + o.error_reasons + '\n');
                }

                textarea.val(textarea.val() + '----[ ' + o.status + ' ]----\n');

                if (o.error_reasons)
                {
                    $.each(o.error_reasons, function(key, reason) {
                        textarea.val(textarea.val() + '+ ' + reason + '\n');
                    })
                }

                textarea.val(textarea.val() + '--------------------------------\n\n');
            })
        }

        $('#modal-error').find('.modal-body').append(textarea)
    }
@endpush
