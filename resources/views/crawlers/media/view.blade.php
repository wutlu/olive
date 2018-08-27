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
    ],
    'dock' => true
])

@push('local.scripts')
    $('[data-length]').characterCounter()

    $(document).on('click', '[data-trigger=delete]', function() {
        var mdl = modal({
                'id': 'alert',
                'body': 'Bu bot silindiğinde, içerdiği tüm veriler de kaybolacaktır. Silme işlemini onaylıyor musunuz?',
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
                        'class': 'waves-effect btn red json',
                        'html': buttons.ok,
                        'data-include': 'id',
                        'data-href': '{{ route('crawlers.media.bot') }}',
                        'data-method': 'delete',
                        'data-callback': '__delete'
                    })
               ])
    })

    function __delete(__, obj)
    {
        if (obj.status == 'ok')
        {
            location.href = '{{ route('crawlers.media.list') }}';
        }
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
                        <small class="grey-text d-block">Elasticsearch</small>
                        @if ($crawler->elasticsearch_index_name)
                            <p class="d-block">{{ $crawler->elasticsearch_index_name }}</p>
                            <p class="grey-text d-block">
                                <span data-name="data-count">10.124</span> içerik
                            </p>
                            <p class="grey-text d-block">
                                <span data-name="link-count">1.414</span> alınacak
                            </p>
                            <p class="grey-text d-block">
                                <span data-name="index-size">124.562</span> byte
                            </p>
                        @else
                            <a href="#" class="btn-flat waves-effect">Index Oluştur</a>
                        @endif
                    </li>
                    <li class="item">
                        <small class="grey-text d-block">Hata</small>
                        <span class="grey-text" data-name="error-count">{{ $crawler->error_count }}</span>
                    </li>
                    <li class="item">
                        <small class="grey-text d-block">Son Kontrol</small>
                        <p class="d-block" data-name="last-control">{{ date('d.m.Y H:i', strtotime($crawler->control_date)) }}</p>
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
                                    <input name="link" id="link" value="{{ $crawler->link }}" type="text" class="validate" data-length="255" />
                                    <label for="link">Ana Sayfa</label>
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
                            <input name="pattern_url" id="pattern_url" value="{{ $crawler->pattern_url }}" type="text" class="validate" data-length="255" />
                            <label for="pattern_url">Makale URL Deseni</label>
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
                <a href="#" class="btn-flat waves-effect waves-red red-text" data-trigger="delete">Sil</a>
                <button type="submit" class="btn waves-effect" data-tigger="submit">Test ve Kayıt</button>
            </div>
        </div>
    </form>
@endsection

@push('local.scripts')
    $(document).on('click', '[data-trigger=submit]', function() {
        var __ = $(this);

            __.html('Yükleniyor...').addClass('disabled')

        vzAjax($('<div />', {
            'data-target': 'form#details-form'
        }))
    })

    function __test(__, obj)
    {
        if (obj.status == 'ok')
        {
            var results = $('[data-name=test-results]');
                results.find('[data-name=count]').html(obj.count.acceptable + '/' + obj.count.total)

                results.children('.collection')
                       .addClass('d-none')
                       .children('.collection-item:not(.model)')
                       .remove()

            if (obj.count.acceptable)
            {
                results.children('.collection')
                       .removeClass('d-none')

                $.each(obj.links, function(key, o) {
                    var item = results.children('.collection').children('.collection-item.model').clone();
                        item.removeClass('d-none model')
                        item.attr('href', o.link)
                        item.find('[data-name=title]').html(o.title)
                        item.find('[data-name=description]').html(o.description)
                        item.find('[data-name=created-at]').html(o.created_at)

                        item.appendTo(results.children('.collection'))
                })

                scrollTo({
                    'target': '[data-name=test-results]',
                    'tolerance': '-72px'
                })
            }

            M.toast({ html: 'Test Başarılı!', classes: 'green' })
        }
        else if (obj.status == 'warn')
        {
            M.toast({ html: 'Test Başarısız!', classes: 'red' })

            if (obj.errors.length)
            {
                var mdl = modal({
                    'id': 'alert',
                    'body': '',
                    'size': 'modal-large',
                    'title': 'Test Hataları',
                    'options': {}
                });

                $.each(obj.errors, function(key, o) {
                    $('#modal-alert').find('.modal-content')
                                     .append(
                                        $('<pre />', {
                                            'class': 'red',
                                            'html': o.reason,
                                            'style': 'padding: 1rem; overflow: auto;'
                                        })
                                     )
                })
            }
        }
        else if (obj.status == 'out_of_date')
        {
            M.toast({ html: 'Kaynak sitede bulunan içerikler eski!', classes: 'orange' })
        }

        $('[data-trigger=submit]').html('{{ 'Test ve Kayıt' }}').removeClass('disabled')
    }
@endpush

@section('dock')
    <div class="card" data-name="test-results">
        <div class="card-content">
            Test Raporu
            <span class="badge" data-name="count">0/0</span>
        </div>
        <div class="collection d-none">
            <a target="_blank" href="#" class="collection-item waves-effect model d-none">
                <span data-name="title"></span>
                <small class="grey-text d-block" data-name="created-at"></small>
                <p class="grey-text" data-name="description"></p>
            </a>
        </div>
    </div>
@endsection
