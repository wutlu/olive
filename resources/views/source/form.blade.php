@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'link' => route('sources.index'),
            'text' => 'Kaynak Tercihleri'
        ],
        [
            'text' => $query ? $query->name : 'Kaynak Tercihi Oluştur'
        ]
    ],
    'footer_hide' => true
])

@push('local.scripts')
    function __action(__, obj)
    {
        if (obj.status == 'ok')
        {
            if (obj.data.status == 'created')
            {
                window.location = obj.data.route
            }
            else if (obj.data.status == 'updated')
            {
                M.toast({ html: 'Kaynak Tercihi Güncellendi', 'classes': 'green' })
            }
        }
    }

    count_update()

    function count_update()
    {
        var media = 0;
        var blog = 0;
        var sozluk = 0;
        var shopping = 0;

        $.each($('input[data-option]'), function() {
            var __ = $(this);

            if (__.prop('checked'))
            {
                switch (__.data('option'))
                {
                    case 'media': media++; break;
                    case 'blog': blog++; break;
                    case 'sozluk': sozluk++; break;
                    case 'shopping': shopping++; break;
                }
            }
        })

        $('[data-count=media]').html(media)
        $('[data-count=blog]').html(blog)
        $('[data-count=sozluk]').html(sozluk)
        $('[data-count=shopping]').html(shopping)
    }

    $(document).on('change', 'input[data-option]', count_update)
    $('input[name=name]').characterCounter()
@endpush

@section('content')
    <form
        name="form"
        id="form"
        method="post"
        action="{{ $query ? route('sources.form', $query->id) : route('sources.form') }}"
        class="json"
        data-method="post"
        data-callback="__action">
        <div class="card mb-1">
            <div class="card-image">
                <img src="{{ asset('img/md-s/21.jpg') }}" alt="Card" />
                <span class="card-title d-flex">
                    <a class="btn-floating white waves-effect mr-1 align-self-center" href="{{ route('sources.index') }}">
                        <i class="material-icons grey-text text-darken-2">chevron_left</i>
                    </a>
                    <span class="align-self-center">{{ $query ? $query->name : 'Kaynak Tercihi Oluştur' }}</span>
                </span>
            </div>
            <div class="card-content">
                <div class="input-field">
                    <input name="name" id="name" type="text" class="validate" data-length="24" value="{{ @$query->name }}" />
                    <label for="name">Kaynak Tercihi Adı</label>
                    <span class="helper-text">E-posta adresiniz veya kullanıcı adınız.</span>
                </div>
            </div>
            <ul class="collapsible">
                @foreach ([
                    'media' => 'Haber',
                    'blog' => 'Blog',
                    'sozluk' => 'Sözlük',
                    'shopping' => 'E-ticaret',
                ] as $key => $title)
                    <li>
                        <div class="collapsible-header waves-effect">
                            <span>
                                {{ $title }} Kaynakları <span class="grey darken-2 white-text" style="padding: 2px 6px;" data-count="{{ $key }}">0</span>
                            </span>
                            <i class="material-icons arrow">keyboard_arrow_down</i>
                        </div>
                        <div class="collapsible-body">
                            @if (count($sources->{$key}))
                                <div class="item-group p-1"> 
                                    @foreach ($sources->{$key} as $item)
                                        <label class="item">
                                            <input
                                                data-option="{{ $key }}"
                                                data-multiple="true"
                                                name="sources_{{ $key }}"
                                                id="{{ $key }}-{{ $item->id }}"
                                                value="{{ $item->id }}"
                                                type="checkbox"
                                                {{ @in_array($item->id, $query->{'source_'.$key}) ? 'checked' : '' }} />
                                            <span class="{{ $item->status ? '' : 'red-text' }}">{{ $item->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-1">
                                    @component('components.nothing')
                                        @slot('text', 'Seçim yapabileceğiniz bir kaynak bulunmuyor.')
                                        @slot('text_class', 'grey-text text-darken-2')
                                    @endcomponent
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="card-content right-align">
                <div class="grey-text text-darken-2">
                    @component('components.alert')
                        @slot('text', 'Kaynağın kırmızı görünmesi geçici olarak devre dışı anlamına gelir.')
                        @slot('icon', 'info')
                    @endcomponent
                    @component('components.alert')
                        @slot('text', 'Listede olmayan yeni bir kaynak isteği için, <a href="'.route('settings.support', 'kaynak-istegi').'">DESTEK</a> sayfamızdan bizimle iletişime geçebilirsiniz.')
                        @slot('icon', 'info')
                    @endcomponent
                </div>
                @if ($query)
                    <a href="#" class="btn-flat red-text waves-effect waves-red" data-trigger="delete">Sil</a>
                @endif
                <button type="submit" class="btn-flat waves-effect">{{ $query ? 'Güncelle' : 'Oluştur' }}</button>
            </div>
        </div>
    </form>
@endsection

@if ($query)
    @push('local.scripts')
        $(document).on('click', '[data-trigger=delete]', function() {
            return modal({
                'id': 'alert',
                'body': 'Silmek istediğinizden emin misiniz?',
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
                        'data-href': '{{ route('sources.delete') }}',
                        'data-method': 'delete',
                        'data-id': '{{ $query->id }}',
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
                $('#modal-alert').modal('close')

                M.toast({ html: 'Kaynak Tercihi Silindi!', 'classes': 'green' })

                setTimeout(function() {
                    window.location = '{{ route('sources.index') }}';
                }, 600)
            }
        }
    @endpush
@endif
