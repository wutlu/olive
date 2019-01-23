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
            'text' => 'Medya Botları',
            'link' => route('crawlers.media.list')
        ],
        [
            'text' => '🐞 '.$crawler->name
        ]
    ]
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
        var mdl = modal({
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
                        'class': 'waves-effect btn-flat cyan-text json',
                        'html': buttons.ok,
                        'data-href': '{{ route('crawlers.media.bot.status') }}',
                        'data-id': '{{ $crawler->id }}',
                        'data-method': 'post',
                        'data-callback': '__status'
                    })
                ]
            });
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
@endpush

@section('content')
    <form
        method="patch"
        action="{{ route('crawlers.media.bot') }}"
        class="json"
        id="details-form"
        data-callback="__test">
        <input type="hidden" value="{{ $crawler->id }}" name="id" id="id" />
        <div class="card">
            <div class="card-content">
                <span class="card-title mb-0">
                    <span>
                        <span data-name="crawler-title">{{ $crawler->name }}</span>
                        <sub data-name="error-count"></sub>
                    </span>
                    <time class="timeago d-block" data-name="control-date"></time>
                </span>
            </div>
            <table
                id="stats"
                class="grey darken-4 load"
                data-method="post"
                data-timeout="4000"
                data-href="{{ route('crawlers.media.bot.statistics', $crawler->id) }}"
                data-callback="__stats"
                data-error-callback="__connection_failed">
                <tbody>
                    <tr>
                        <th class="center-align">
                            <button type="submit" class="btn-flat waves-effect cyan-text">
                                <i class="material-icons">done_all</i>
                            </button>
                        </th>
                        <th class="center-align">
                            <a href="#" data-trigger="status" class="btn-flat waves-effect waves-{{ $crawler->status ? 'green green' : 'red red' }}-text">{{ $crawler->status ? 'AKTİF' : 'PASİF' }}</a>
                        </th>

                        <th class="right-align grey-text">KUYRUK</th>
                        <th class="orange-text" data-elasticsearch data-name="total-docs-buffer">-</th>

                        <th class="right-align grey-text">BAŞARILI</th>
                        <th class="orange-text" data-elasticsearch data-name="total-docs-success">-</th>

                        <th class="right-align grey-text">BAŞARISIZ</th>
                        <th class="orange-text" data-elasticsearch data-name="total-docs-failed">-</th>
                    </tr>
                </tbody>
            </table>
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
                                    <small class="helper-text">Veri toplanacak sitenin Ana Sayfa http(s) adresi.</small>
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
                    @push('local.scripts')
                        $(document).on('click', '[data-id=match]', function() {
                            var __ = $(this);
                            var url_pattern = $('input[name=url_pattern]');
                                url_pattern.val(url_pattern.val() + __.data('pattern'))
                                url_pattern.focus()

                                M.updateTextFields()
                        }).on('keyup', '[name=site]', function() {
                            var __ = $(this);
                            var title = __.val().replace(/(.+)(\.|\/)(.{4,})\.(.+)/, '$3')
                                                .replace('i', 'İ')
                                                .replace('i', 'İ')
                                                .replace('ü', 'Ü')
                                                .replace('ü', 'Ü')
                                                .replace('ö', 'Ö')
                                                .replace('ö', 'Ö')
                                                .replace('ü', 'Ç')
                                                .replace('ü', 'Ç')
                                                .replace('ğ', 'Ğ')
                                                .replace('ğ', 'Ğ')
                                                .replace('ş', 'Ş')
                                                .replace('ş', 'Ş')
                                                .toUpperCase();

                            $('input[name=name]').val(title)
                            $('[data-name=breadcrumb]').html(title)
                        })
                    @endpush
                    <div class="collection-item green lighten-4">
                        <div class="input-field">
                            <input name="url_pattern" id="url_pattern" value="{{ $crawler->url_pattern }}" type="text" class="validate" data-length="255" />
                            <label for="url_pattern">Makale URL Deseni</label>
                            <small class="helper-text">Kaynak içerik adreslerinin <strong>REGEX</strong> deseni.</small>
                        </div>
                        @include('crawlers._inc.regex')
                    </div>
                    <div class="collection-item">
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
                                    <input name="control_interval" id="control_interval" value="{{ $crawler->control_interval }}" type="number" class="validate" max="60" min="1" />
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
                </div>
            </div>
            <div class="card-content yellow lighten-4">Bu aşamada girilen değerler test edilir. Test sonucu olumlu olmadığı sürece değerler kaydedilmeyecektir.</div>
            <div class="card-action right-align">
                <a href="#" class="btn-flat waves-effect red-text" data-trigger="delete">
                    <i class="material-icons">close</i>
                </a>
                <button type="submit" class="btn waves-effect">
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
    $(document).on('click', '[data-trigger=delete]', function() {
        var mdl = modal({
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
                        'data-href': '{{ route('crawlers.media.bot') }}',
                        'data-id': '{{ $crawler->id }}',
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
            location.href = '{{ route('crawlers.media.list') }}';
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
                    'class': 'modal-close waves-effect btn-flat cyan-text',
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
