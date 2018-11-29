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
            'text' => 'Sözlük Botları',
            'link' => route('crawlers.sozluk.list')
        ],
        [
            'text' => $crawler->name
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
                'options': {}
            });

            mdl.find('.modal-footer')
               .html([
                    $('<a />', {
                        'href': '#',
                        'class': 'modal-close waves-effect btn-flat',
                        'html': buttons.cancel
                    }),
                    $('<span />', {
                        'html': ' '
                    }),
                    $('<a />', {
                        'href': '#',
                        'class': 'waves-effect btn json',
                        'html': buttons.ok,
                        'data-href': '{{ route('crawlers.sozluk.bot.status') }}',
                        'data-id': '{{ $crawler->id }}',
                        'data-method': 'post',
                        'data-callback': '__status'
                    })
               ])
    })

    var statTimer;

    function __stats(__, obj)
    {
        if (obj.status == 'ok')
        {
            var data_id_input = $('[data-name=last-id]');

            if (data_id_input.attr('data-id') != obj.data.crawler.last_id)
            {
                data_id_input.val(obj.data.crawler.last_id)
                data_id_input.data('id', obj.data.crawler.last_id)
            }

            $('[data-name=error-count]').html(obj.data.crawler.error_count + ' hata')

            if (obj.data.elasticsearch.status == 'ok' && obj.data.elasticsearch.data._all.primaries.docs)
            {
                $('[data-name=total-docs]').html(number_format(obj.data.elasticsearch.data._all.primaries.docs.count))
                $('[data-name=total-size]').html(humanFileSize(obj.data.elasticsearch.data._all.primaries.store.size_in_bytes))
            }
            else
            {
                var message = $.parseJSON(obj.data.elasticsearch.message);

                $('[data-name=total-docs]').html(message.status == 404 ? 'Index Oluşturulmadı!' : 'Bağlantı Hatası')
                $('[data-name=total-size]').html(message.status == 404 ? 'Index Oluşturulmadı!' : 'Bağlantı Hatası')
            }

            window.clearTimeout(statTimer)

            statTimer = setTimeout(function() {
                vzAjax($('#stats'))
            }, 10000)
        }
    }
@endpush

@section('content')
    <form
        method="patch"
        action="{{ route('crawlers.sozluk.bot') }}"
        class="json"
        id="details-form"
        data-callback="__test">
        <input type="hidden" value="{{ $crawler->id }}" name="id" id="id" />
        <div class="card">
            <table id="stats" class="grey darken-4 load" data-href="{{ route('crawlers.sozluk.bot.statistics', $crawler->id) }}" data-callback="__stats">
                <tbody>
                    <tr>
                        <th class="right-align grey-text">BOYUT</th>
                        <th class="orange-text" data-name="total-size"></th>

                        <th class="right-align grey-text">DÖKÜMAN</th>
                        <th class="orange-text" data-name="total-docs"></th>
                    </tr>
                </tbody>
            </table>
            <div class="card-content">
                <a href="#" data-trigger="status" class="btn-flat waves-effect waves-{{ $crawler->status ? 'green green' : 'red red' }}-text">{{ $crawler->status ? 'AKTİF' : 'PASİF' }}</a>

                @if (!$crawler->status && $crawler->off_reason)
	                <div>
	                    <small class="grey-text">Kapanma Nedeni</small>
	                    <p class="d-block">{{ $crawler->off_reason }}</p>
	                </div>
                @endif
            </div>
            <div class="card-image">
                <img src="{{ asset('img/card-header.jpg') }}" alt="{{ $crawler->name }}" />
                <span class="card-title">
                    <span data-name="crawler-title">{{ $crawler->name }}</span>
                    <sub data-name="error-count"></sub>
                </span>
            </div>
            <div class="card-content">
                <div class="collection">
                    <div class="collection-item">
                        @push('local.scripts')
                            $(document).on('keydown keyup', 'input[name=name]', function() {
                                var __ = $(this);

                                $('[data-name=crawler-title]').html(__.val())
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
                                    <input name="url_pattern" id="url_pattern" value="{{ $crawler->url_pattern }}" type="text" class="validate" data-length="255" />
                                    <label for="url_pattern">Döküman URL Deseni</label>
                                    <small class="helper-text">Id kısmını <span class="red-text">__id__</span> olarak belirtin.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="collection-item">
                        <div class="d-flex flex-wrap">
                            <div style="min-width: 50%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="selector_title" id="selector_title" value="{{ $crawler->selector_title }}" type="text" class="validate" data-length="255" />
                                    <label for="selector_title">Döküman Başlık Seçicisi</label>
                                    <small class="helper-text">İçerik başlığının CSS seçicisi.</small>
                                </div>
                            </div>
                            <div style="min-width: 50%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="selector_entry" id="selector_entry" value="{{ $crawler->selector_entry }}" type="text" class="validate" data-length="255" />
                                    <label for="selector_entry">Döküman Açıklama Seçicisi</label>
                                    <small class="helper-text">İçeriğin CSS seçicisi.</small>
                                </div>
                            </div>
                            <div style="min-width: 50%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="selector_author" id="selector_author" value="{{ $crawler->selector_author }}" type="text" class="validate" data-length="255" />
                                    <label for="selector_author">Döküman Yazar Seçicisi</label>
                                    <small class="helper-text">Yazar adının CSS seçicisi.</small>
                                </div>
                            </div>
                            <div style="min-width: 50%; padding: 1rem;">
                                <div class="input-field">
                                    <input data-id="{{ $crawler->last_id }}" data-name="last-id" name="last_id" id="last_id" value="{{ $crawler->last_id }}" type="number" class="validate" min="0" />
                                    <label for="last_id">Son Id</label>
                                    <small class="helper-text">Son alınan içeriğin kimlik numarası.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="collection-item">
                        <div class="d-flex flex-wrap">
                            <div style="width: 50%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="off_limit" id="off_limit" value="{{ $crawler->off_limit }}" type="number" class="validate" max="1000" min="10" />
                                    <label for="off_limit">Kapatma Limiti</label>
                                    <small class="helper-text">
                                        Belirtilen değer kadar hata alındığı takdirde; hata logu girilir ve bot devre dışı bırakılır.
                                    </small>
                                </div>
                            </div>
                            <div style="width: 50%; padding: 1rem;">
                                <div class="input-field">
                                    <input name="max_attempt" id="max_attempt" value="{{ $crawler->max_attempt }}" type="number" class="validate" max="10000" min="10" />
                                    <label for="max_attempt">Hatalı Deneme Sayısı</label>
                                    <small class="helper-text">Girilen değer kadar içerik kontrol edildikten sonra kontrol tamamlanır.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-content yellow lighten-4">Bu aşamada girilen değerler test edilir. Test sonucu olumlu olmadığı sürece değerler kaydedilmeyecektir.</div>
            <div class="card-action right-align">
                <a href="#" class="btn-flat waves-effect" data-trigger="delete">
                    <i class="material-icons red-text">close</i>
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
                'body': 'Bu kaydı silmek üzeresiniz?',
                'size': 'modal-small',
                'title': 'Sil',
                'options': {}
            });

            mdl.find('.modal-footer')
               .html([
                    $('<a />', {
                        'href': '#',
                        'class': 'modal-close waves-effect btn-flat',
                        'html': buttons.cancel
                    }),
                    $('<span />', {
                        'html': ' '
                    }),
                    $('<a />', {
                        'href': '#',
                        'class': 'waves-effect btn json',
                        'html': buttons.ok,
                        'data-href': '{{ route('crawlers.sozluk.bot') }}',
                        'data-id': '{{ $crawler->id }}',
                        'data-method': 'delete',
                        'data-callback': '__delete'
                    })
               ])
    })

    function __delete(__, obj)
    {
        if (obj.status == 'ok')
        {
            location.href = '{{ route('crawlers.sozluk.list') }}';
        }
    }

    function __test(__, obj)
    {
        var mdl = modal({
            'id': 'error',
            'body': '',
            'size': 'modal-large',
            'title': obj.status == 'ok' ? 'Test Başarılı!' : 'Test Başarısız!',
            'options': {}
        });

        mdl.removeClass('red green')
        mdl.addClass(obj.status == 'ok' ? 'green' : 'red')

            mdl.find('.modal-footer')
               .html([
                    $('<a />', {
                        'href': '#',
                        'class': 'modal-close waves-effect btn-flat',
                        'html': buttons.ok
                    })
               ])

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
                    textarea.val(textarea.val() + '- ' + o.data.author + '\n');
                    textarea.val(textarea.val() + '- ' + o.data.entry + '\n');
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
