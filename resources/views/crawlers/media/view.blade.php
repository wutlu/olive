@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Bot Yönetimi'
        ],
        [
            'text' => 'Medya Botları',
            'link' => route('crawlers.media.list')
        ],
        [
            'text' => $crawler->name
        ]
    ]
])

@push('local.scripts')
    $('[data-length]').characterCounter()
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
            <div class="card-image">
                <img src="{{ asset('img/md-s/36.jpg') }}" alt="{{ $crawler->name }}" />
                <span class="card-title">{{ $crawler->name }}</span>
            </div>
            <div class="card-content grey lighten-2">
                <ul class="item-group">
                    <li class="item">
                        @push('local.scripts')
                            function __status(__, obj)
                            {
                                if (obj.status == 'ok')
                                {
                                    var __ = $('[data-trigger=status]');

                                        __.removeClass('waves-red red-text waves-green green-text')

                                    if (obj.data.status)
                                    {
                                        __.addClass('waves-green green-text').html('AKTİF')

                                        $('[data-name=error-count]').html('0')
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
                                                'data-href': '{{ route('crawlers.media.bot.status') }}',
                                                'data-id': '{{ $crawler->id }}',
                                                'data-method': 'post',
                                                'data-callback': '__status'
                                            })
                                       ])
                            })
                        @endpush
                        <small class="grey-text d-block">Durum</small>
                        <a href="#" data-trigger="status" class="btn-flat waves-effect waves-{{ $crawler->status ? 'green green' : 'red red' }}-text">{{ $crawler->status ? 'AKTİF' : 'PASİF' }}</a>
                    </li>
                    <li class="item">
                        @if ($crawler->elasticsearch_index)
                            <small class="grey-text d-block">Toplam Döküman</small>
                            <p class="d-block">{{ number_format($crawler->indexStats()->data['_all']['primaries']['docs']['count']) }}</p>
                            <small class="grey-text d-block">Kapladığı Alan</small>
                            <p class="d-block">{{ Term::humanFileSize($crawler->indexStats()->data['_all']['primaries']['store']['size_in_bytes'])->readable }}</p>
                        @else
                            <small class="grey-text d-block">Elasticsearch</small>
                            <i class="material-icons red-text">close</i>
                        @endif
                    </li>
                    <li class="item">
                        <small class="grey-text d-block">Hata</small>
                        <span class="grey-text">{{ $crawler->error_count }}</span>
                    </li>
                    <li class="item">
                        <small class="grey-text d-block">Son Kontrol</small>
                        <p class="d-block">{{ date('d.m.Y H:i', strtotime($crawler->control_date)) }}</p>
                    </li>
                </ul>
                @if (!$crawler->status && $crawler->off_reason)
                <small class="grey-text">Kapanma Nedeni</small>
                <p class="d-block">{{ $crawler->off_reason }}</p>
                @endif
            </div>
            <div class="card-content">
                <div class="collection">
                    <div class="collection-item">
                        @push('local.scripts')
                            $(document).on('keydown keyup', 'input[name=name]', function() {
                                var __ = $(this);

                                $('span.card-title').html(__.val())
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
                    <div class="collection-item">
                        <div class="input-field">
                            <input name="url_pattern" id="url_pattern" value="{{ $crawler->url_pattern }}" type="text" class="validate" data-length="255" />
                            <label for="url_pattern">Makale URL Deseni</label>
                            <small class="helper-text">Kaynak içerik adreslerinin <strong>REGEX</strong> deseni.</small>
                        </div>
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
                                    <input name="off_limit" id="off_limit" value="{{ $crawler->off_limit }}" type="number" class="validate" max="100" min="10" />
                                    <label for="off_limit">Kapatma Limiti</label>
                                    <small class="helper-text">
                                        Belirtilen rakam kadar hata alındığı takdirde; hata logu girilir ve bot devre dışı bırakılır.
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
                </div>
            </div>
            <div class="card-action right-align">
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
                textarea.val(textarea.val() + '- ' + o.data.title + '\n');
                textarea.val(textarea.val() + '- ' + o.data.description + '\n');
                textarea.val(textarea.val() + '- ' + o.data.created_at + '\n');

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
