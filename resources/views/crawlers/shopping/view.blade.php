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
            'text' => 'E-ticaret Botları',
            'link' => route('crawlers.shopping.list')
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
                        'class': 'waves-effect btn-flat json',
                        'html': buttons.ok,
                        'data-href': '{{ route('crawlers.shopping.bot.status') }}',
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


            if (obj.data.elasticsearch.message)
            {
                var message = $.parseJSON(obj.data.elasticsearch.message);
            }
            else
            {
                var message = { 'status': obj.data.elasticsearch.status == 'ok' ? 200 : 404 };
            }

            if (message.status == '404')
            {
                $('[data-elasticsearch]').html('Index Oluşturulmadı!')
            }
            else
            {
                $('[data-name=total-docs-success]').html(number_format(obj.data.count.success.data.count))
                $('[data-name=total-docs-failed]').html(number_format(obj.data.count.failed.data.count))
                $('[data-name=total-docs-buffer]').html(number_format(obj.data.count.buffer.data.count))
                $('[data-name=total-size]').html(humanFileSize(obj.data.elasticsearch.data._all.primaries.store.size_in_bytes))
            }

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
        action="{{ route('crawlers.shopping.bot') }}"
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
            @if (!$crawler->status && $crawler->off_reason)
                <div class="card-content red white-text">
                    <small class="black-text">Kapanma Nedeni</small>
                    <p class="d-block">{{ $crawler->off_reason }}</p>
                </div>
            @endif
            <div
                id="stats"
                class="item-group grey lighten-5 p-2 load"
                data-method="post"
                data-timeout="4000"
                data-href="{{ route('crawlers.shopping.bot.statistics', $crawler->id) }}"
                data-callback="__stats"
                data-error-callback="__connection_failed">
                <span class="item">
                    <button type="submit" class="btn-flat waves-effect cyan-text d-flex">
                        <i class="material-icons mr-1">done_all</i> TEST
                    </button>
                </span>
                <span class="item">
                    <a href="#" data-trigger="status" class="btn-flat waves-effect waves-{{ $crawler->status ? 'green green' : 'red red' }}-text">{{ $crawler->status ? 'AKTİF' : 'PASİF' }}</a>
                </span>

                <div class="item">
                    <small class="grey-text">BOYUT</small>
                    <span class="d-block" data-elasticsearch data-name="total-size">-</span>
                </div>

                <div class="item">
                    <small class="grey-text">KUYRUK/BAŞARILI/BAŞARISIZ</small>
                    <div class="d-flex">
                        <span data-elasticsearch data-name="total-docs-buffer">-</span>
                        <span>/</span>
                        <span data-elasticsearch data-name="total-docs-success">-</span>
                        <span>/</span>
                        <span data-elasticsearch data-name="total-docs-failed">-</span>
                    </div>
                </div>
            </div>
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
                            <div style="min-width: 30%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="site" id="site" value="{{ $crawler->site }}" type="text" class="validate" data-length="255" />
                                    <label for="site">Ana Sayfa</label>
                                    <small class="helper-text">Veri toplanacak sitenin Ana Sayfa http(s) adresi.</small>
                                </div>
                            </div>
                            <div style="max-width: 30%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="google_search_query" id="google_search_query" value="{{ $crawler->google_search_query }}" type="text" class="validate" data-length="255" />
                                    <label for="google_search_query">Google Arama Sorgusu</label>
                                    <small class="helper-text">Ürün bağlantılarının tespiti için ilgili siteden içerikleri elde etme amaçlı sorgu sonucu.<br />Bkz: site:sahibinden.com/ilan</small>
                                </div>
                            </div>
                            <div style="max-width: 20%; padding: 1rem;">
                                <div class="input-field">
                                    <select name="google_max_page" id="google_max_page">
                                        @for ($i = 1; $i <= 10; $i++)
                                            <option value="{{ $i }}" @if ($crawler->google_max_page == $i){{ 'selected' }}@endif>{{ $i }}</option>
                                        @endfor
                                    </select>
                                    <label for="google_max_page">Google Maksimum Sayfa Sayısı</label>
                                    <small class="helper-text">Google sonuçları alınırken gidilecek maksimum sayfa sayısı. (Her sayfada 10 kayıt bulunur)</small>
                                </div>
                            </div>
                            <div style="max-width: 20%; padding: 1rem;">
                                <div class="input-field">
                                    <select name="google_time" id="google_time">
                                            <option value="h" @if ($crawler->google_time == 'h'){{ 'selected' }}@endif>1 Saat</option>
                                            <option value="d" @if ($crawler->google_time == 'd'){{ 'selected' }}@endif>1 Gün</option>
                                            <option value="w" @if ($crawler->google_time == 'w'){{ 'selected' }}@endif>1 Hafta</option>
                                            <option value="m" @if ($crawler->google_time == 'm'){{ 'selected' }}@endif>1 Ay</option>
                                            <option value="y" @if ($crawler->google_time == 'y'){{ 'selected' }}@endif>1 Yıl</option>
                                    </select>
                                    <label for="google_max_page">Google Zaman Sınırlaması</label>
                                    <small class="helper-text">Google sonuçları alınırken gidilecek maksimum zaman.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @push('local.scripts')
                    $('select').formSelect()

                    $(document).on('click', '[data-id=match]', function() {
                        var __ = $(this);
                        var url_pattern = $('input[name=url_pattern]');
                            url_pattern.val(url_pattern.val() + __.data('pattern'))

                            M.updateTextFields()
                    })
                    @endpush
                    <div class="collection-item green lighten-5 z-depth-1">
                        <div class="input-field">
                            <input name="url_pattern" id="url_pattern" value="{{ $crawler->url_pattern }}" type="text" class="validate" data-length="255" />
                            <label for="url_pattern">Ürün URL Deseni</label>
                            <small class="helper-text">Kaynak içerik adreslerinin <strong>REGEX</strong> deseni.</small>
                        </div>
                        @include('crawlers._inc.regex')
                    </div>
                    <div class="collection-item">
                        <div class="d-flex flex-wrap">
                            <div style="min-width: 50%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="selector_title" id="selector_title" value="{{ $crawler->selector_title }}" type="text" class="validate" data-length="255" />
                                    <label for="selector_title">Ürün Başlık Seçicisi</label>
                                    <small class="helper-text">Kaynak ürün başlığının CSS seçicisi.</small>
                                </div>
                            </div>
                            <div style="min-width: 50%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="selector_description" id="selector_description" value="{{ $crawler->selector_description }}" type="text" class="validate" data-length="255" />
                                    <label for="selector_description">Ürün Açıklama Seçicisi</label>
                                    <small class="helper-text">Kaynak ürün açıklamasının CSS seçicisi.</small>
                                </div>
                            </div>
                            <div style="min-width: 50%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="selector_breadcrumb" id="selector_breadcrumb" value="{{ $crawler->selector_breadcrumb }}" type="text" class="validate" data-length="255" />
                                    <label for="selector_breadcrumb">Ürün Mini Haritası (Dize)</label>
                                    <small class="helper-text">Kaynak ürün için mini harita (breadcrumb) CSS seçicisi.</small>
                                </div>
                            </div>
                            <div style="min-width: 50%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="selector_address" id="selector_address" value="{{ $crawler->selector_address }}" type="text" class="validate" data-length="255" />
                                    <label for="selector_address">Ürün Adres Seçicisi (Dize)</label>
                                    <small class="helper-text">Kaynak ürün adresinin CSS seçicisi.</small>
                                </div>
                            </div>
                            <div style="min-width: 50%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="selector_seller_name" id="selector_seller_name" value="{{ $crawler->selector_seller_name }}" type="text" class="validate" data-length="255" />
                                    <label for="selector_seller_name">Satıcı Adı Seçicisi</label>
                                    <small class="helper-text">Satıcı adı CSS seçicisi.</small>
                                </div>
                            </div>
                            <div style="min-width: 50%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="selector_seller_phones" id="selector_seller_phones" value="{{ $crawler->selector_seller_phones }}" type="text" class="validate" data-length="255" />
                                    <label for="selector_seller_phones">Satıcı Telefonu Seçicisi</label>
                                    <small class="helper-text">Satıcı telefon numarası CSS seçicisi.</small>
                                </div>
                            </div>
                            <div style="min-width: 50%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="selector_price" id="selector_price" value="{{ $crawler->selector_price }}" type="text" class="validate" data-length="255" />
                                    <label for="selector_price">Fiyat Seçicisi</label>
                                    <small class="helper-text">Ürün fiyatı CSS seçicisi.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr />
                    <div class="collection-item">
                        <div class="d-flex flex-wrap">
                            <div style="width: 50%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="off_limit" id="off_limit" value="{{ $crawler->off_limit }}" type="number" class="validate" max="100" min="10" />
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
                                    <small class="helper-text" data-name="minute">Girilen değer aralığında içerik kontrolü yapılsın.</small>
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
                        'data-href': '{{ route('crawlers.shopping.bot') }}',
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
            location.href = '{{ route('crawlers.shopping.list') }}';
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
                    textarea.val(textarea.val() + '- 1: ' + o.data.title + '\n');
                    textarea.val(textarea.val() + '- 2: ' + o.data.description + '\n');
                    textarea.val(textarea.val() + '- 3: ' + o.data.created_at + '\n');

                    textarea.val(textarea.val() + '- 4: ' + o.data.address + '\n');
					textarea.val(textarea.val() + '- 5: ' + o.data.breadcrumb + '\n');
					textarea.val(textarea.val() + '- 6: ' + o.data.seller_name + '\n');
                    textarea.val(textarea.val() + '- 7: ' + o.data.seller_phones + '\n');

					textarea.val(textarea.val() + '- 8: ' + o.data.price.amount + '\n');
                    textarea.val(textarea.val() + '- 9: ' + o.data.price.currency + '\n');
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
