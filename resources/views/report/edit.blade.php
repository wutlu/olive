@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Raporlar',
            'link' => route('report.dashboard')
        ],
        [
            'text' => $report->name
        ]
    ],
    'footer_hide' => true,
    'report_menu' => true,
    'help' => count($pages) > 1 ? 'helpStart.start()' : false,
    'dock' => true
])

@section('dock')
    <form id="update" method="post" action="{{ route('report.edit', $report->id) }}" data-callback="__save" class="json" autocomplete="off">
        <div class="card card-unstyled">
            <div class="card-content">
                <div class="input-field">
                    <input name="name"id="name" type="text" class="validate" value="{{ $report->name }}" />
                    <label for="name">Rapor Adı/Başlığı</label>
                </div>
                <div class="input-field">
                    <input name="password"id="password" type="text" class="validate" value="{{ $report->password }}" />
                    <label for="password">Rapor Şifresi</label>
                    <span class="helper-text">Raporu açacak kişiler için rapor şifresi. Boş bırakılırsa, rapor herkes tarafından açılabilir. (İsteğe Bağlı)</span>
                </div>
            </div>
            <div class="card-content">
                <span class="card-title">Tarih</span>
                <div class="input-field">
                    <input placeholder="Tarih 1" name="date_1" id="date_1" type="date" class="validate" value="{{ $report->date_1 }}" />
                    <span class="helper-text">1. Tarih (İsteğe Bağlı)</span>
                </div>
                <div class="input-field">
                    <input placeholder="Tarih 2" name="date_2" id="date_2" type="date" class="validate" value="{{ $report->date_2 }}" />
                    <span class="helper-text">2. Tarih (İsteğe Bağlı)</span>
                </div>
            </div>
            <div class="card-footer right-align">
                <button type="submit" class="btn-flat waves-effect">Güncelle</button>
            </div>
        </div>
    </form>
    <div class="input-field">
        <input type="text" id="url" value="{{ route('report.view', $report->key) }}" data-clip="Panoya Kopyalandı" />
        <label for="url">Rapor Adresi</label>
        <span class="helper-text">Raporu görmesini istediğiniz kişilere bu adresi gönderebilirsiniz.</span>
    </div>
    @push('local.scripts')
        function __save(__, obj)
        {
            if (obj.status == 'ok')
            {
                flash_alert('Rapor Güncellendi!', 'green white-text')
            }
        }
    @endpush
@endsection

@push('local.scripts')
    function __report__page_update(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('body').removeClass('fpw-active')

            $('.collection').children('[data-id=' + obj.data.id + '].collection-item')
                            .effect('highlight', { 'color': '#66bb6a' }, 1000)
                            .find('[data-name=title]')
                            .html(obj.data.title)
        }
    }

    function __report__page(__, obj)
    {
        var action = '';
        var _split = (obj.page.type).split('.')

        switch (_split[1])
        {
            case 'lines':
            case 'title':
                action = '/raporlar/sayfa/' + obj.page.id;
            break;
            case 'article':
            case 'entry':
            case 'media':
            case 'document':
            case 'comment':
            case 'video':
            case 'product':
            case 'tweet':
                action = '/raporlar/icerik/' + obj.page.id;
            break;
            case 'stats':
            case 'chart':
            case 'tr_map':
            case 'twitterMentions':
            case 'twitterInfluencers':
            case 'twitterUsers':
            case 'youtubeUsers':
            case 'youtubeComments':
            case 'sozlukSites':
            case 'sozlukUsers':
            case 'sozlukTopics':
            case 'newsSites':
            case 'blogSites':
            case 'shoppingSites':
            case 'shoppingUsers':
                action = '/raporlar/aggs/' + obj.page.id;
                action = '/raporlar/aggs/' + obj.page.id;
            break;
        }

        var form = __report__page_form(
            {
                'action': action,
                'method': 'patch',
                'callback': '__report__page_update',
                'type': _split[1]
            }
        );

        form.find('input[name=title]').val(obj.page.title)
        form.find('input[name=subtitle]').val(obj.page.subtitle)

        __report__pattern(obj, form, _split[1], 'write')

        form.find('.report-tools').prepend(
            $('<a />', {
                'href': '#',
                'class': 'btn-floating btn-flat red waves-effect',
                'data-trigger': 'report-page_delete',
                'data-id': obj.page.id,
                'html': $('<i />', {
                    'class': 'material-icons',
                    'html': 'delete'
                })
            })
        )

        full_page_wrapper(form)

        form.find('input[name=title]').focus()
    }

    $(document).on('click', '[data-trigger=report-page_delete]', function() {
        return modal({
            'id': 'alert',
            'body': 'Silmek istediğinizden emin misiniz?',
            'size': 'modal-small',
            'title': 'Sil',
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect grey-text btn-flat',
                    'html': keywords.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<a />', {
                    'href': '#',
                    'class': 'waves-effect btn-flat red-text json',
                    'html': keywords.ok,
                    'data-href': '{{ route('report.page.delete') }}',
                    'data-method': 'delete',
                    'data-id': $(this).data('id'),
                    'data-callback': '__report__page_delete'
                })
            ],
            'options': {}
        })
    })

    function __report__page_delete(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('#modal-alert').modal('close')

            $('body').removeClass('fpw-active')

            $('.collection').children('[data-id=' + obj.data.id + '].collection-item')
                            .effect('highlight', { 'color': '#ef5350' }, 400, function() {
                                $(this).slideUp(function() {
                                    $(this).remove()
                                })
                            })
        }
    }
@endpush

@section('content')
    @if (count($pages))
        <ul class="collection collection-unstyled collection-hoverable sortable">
            @foreach ($pages as $page)
                <li class="collection-item d-flex" data-id="{{ $page->id }}">
                    <a href="#" class="btn-floating btn-flat align-self-center handle">
                        <i class="material-icons">drag_handle</i>
                    </a>
                    <div class="flex-fill">
                        <div class="d-flex justify-content-between">
                            <div class="align-self-center ml-1">
                                <span data-name="title" class="word-break-all">{{ $page->title }}</span>
                                <span class="d-table">{{ $types[$page->type] }}</span>
                            </div>
                            <div class="p-1 align-self-center d-flex">
                                @if ($page->image)
                                    <i class="material-icons align-self-center m-1">image</i>
                                @endif
                                <a
                                    href="#"
                                    class="btn-floating btn-flat waves-effect align-self-center json"
                                    data-href="{{ route('report.page', $page->id) }}"
                                    data-method="post"
                                    data-callback="__report__page"
                                    data-type="{{ $page->type }}">
                                    <i class="material-icons">edit</i>
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <div class="p-2 m-2">
            @component('components.nothing')
                @slot('text', 'Bu rapor için henüz içerik oluşturulmadı.')
            @endcomponent
        </div>
    @endif
@endsection

@push('local.scripts')
    $('.sortable').sortable({
        handle: '.handle',
        start: function(event, ui)
        {
            $(ui.item).addClass('blue-grey lighten-4')

            @if (count($pages) > 1)
                helpStart.reset()
            @endif
        },
        stop: function(event, ui)
        {
            $(ui.item).removeClass('blue-grey lighten-4')

            var ids = [];

            $.each($('ul.collection').children('li.collection-item'), function() {
                ids.push($(this).data('id'))
            })

            vzAjax($('<div />', {
                'data-href': '{{ route('report.page.sort') }}',
                'data-method': 'post',
                'data-id': {{ $report->id }},
                'data-ids': JSON.stringify(ids)
            }))
        }
    })

    @if (count($pages) > 1)
        const helpStart = new Driver({
            showButtons: false,
            keyboardControl: false,
            padding: 16,
            onReset: function() {
                @if (!auth()->user()->intro('driver.report.sortable'))
                    vzAjax($('<div />', {
                        'class': 'json',
                        'data-method': 'post',
                        'data-href': '{{ route('intro', 'driver.report.sortable') }}'
                    }))
                @endif
            }
        })

        helpStart.defineSteps([
            {
                element: '.handle',
                popover: {
                    title: 'Yeniden Sıralayın',
                    description: 'Rapor sayfalarını yeniden sıralamak için simgeden tutun ve sürükleyin.',
                    position: 'right'
                }
            }
        ])

        @if (!auth()->user()->intro('driver.report.sortable'))
            helpStart.start()
        @endif
    @endif
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/speakingurl.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
@endpush
