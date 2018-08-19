@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Sayfalar',
            'link' => route('admin.page.list')
        ],
        [
            'text' => @$page ? $page->title : 'Sayfa Oluştur'
        ]
    ]
])

@push('local.scripts')
    function __form(__, obj)
    {
        if (obj.status == 'ok')
        {
            if (obj.data.status == 'created')
            {
                location.href = obj.data.route;
            }
            else if (obj.data.status == 'updated')
            {
                M.toast({ html: 'Sayfa Güncellendi', classes: 'green' })
            }
        }
    }

    $(document).on('click', '[data-trigger=delete]', function() {
        var mdl = modal({
                'id': 'alert',
                'body': 'Silmek istediğinizden emin misiniz?',
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
                        'data-href': '{{ route('admin.page') }}',
                        'data-method': 'delete',
                        'data-callback': '__delete'
                    })
               ])
    })

    function __delete(__, obj)
    {
        if (obj.status == 'ok')
        {
            location.href = '{{ route('admin.page.list') }}';
        }
    }

    @if (session('status') == 'created')
        M.toast({ html: 'Sayfa Oluşturuldu', classes: 'green' })
    @endif
@endpush

@section('content')
    <form method="{{ @$page ? 'patch' : 'put' }}" action="{{ route('admin.page') }}" class="json" id="details-form" data-callback="__form">
        @if (@$page)
        <input type="hidden" value="{{ $page->id }}" name="id" id="id" />
        @endif
        <div class="card">
            <div class="card-image">
                <img src="{{ asset('img/md-s/20.jpg') }}" alt="{{ @$page ? $page->title : 'Sayfa Oluştur' }}" />
                <span class="card-title">{{ @$page ? $page->title : 'Sayfa Oluştur' }}</span>
            </div>
            <div class="card-content">
                <div class="collection">
                    <div class="collection-item">
                        <div class="input-field" style="max-width: 240px;">
                            <input name="title" id="title" value="{{ @$page->title }}" type="text" class="validate" />
                            <label for="title">Sayfa Başlığı</label>
                            <small class="helper-text">Arama sayfalarında ve tarayıcı başlık çubuğunda görünecek sayfa başlığı.</small>
                        </div>
                    </div>
                    @push('local.scripts')
                    $(document).on('keydown keyup', 'input[name=title]', function() {
                        var __ = $(this);

                        $('input[name=slug]').val(slug(__.val()))

                        M.updateTextFields()
                    })
                    @endpush
                    <div class="collection-item">
                        <div class="input-field" style="max-width: 240px;">
                            <input name="slug" id="slug" value="{{ @$page->slug }}" type="text" class="validate" />
                            <label for="slug">Slug</label>
                            <small class="helper-text">Sayfa adresinden sonra gelecek olan alan. {!! url('/ornek') !!}</small>
                        </div>
                    </div>
                    <div class="collection-item">
                        <div class="input-field" style="max-width: 240px;">
                            <input name="keywords" id="keywords" value="{{ @$page->keywords }}" type="text" class="validate" />
                            <label for="keywords">Anahtar Kelimeler</label>
                            <small class="helper-text">Arama sonuçlarında öne çıkartılacak kelimeler.</small>
                        </div>
                    </div>
                    <div class="collection-item">
                        <div class="input-field" style="max-width: 240px;">
                            <input name="description" id="description" value="{{ @$page->description }}" type="text" class="validate" />
                            <label for="description">Sayfa Açıklaması</label>
                            <small class="helper-text">Arama sonuçlarında görünecek açıklama.</small>
                        </div>
                    </div>
                    <div class="collection-item">
                        <div class="input-field">
                            <textarea name="body" id="body" class="materialize-textarea validate">{{ @$page->body }}</textarea>
                            <label for="body">Sayfa Gövdesi</label>
                            <small class="helper-text">Sayfa içeriği. (HTML)</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-action right-align">
                @if (@$page)
                    <a href="#" class="btn-flat waves-effect" data-trigger="delete">Sil</a>
                @endif
                <button type="submit" class="btn waves-effect">{{ @$page ? 'Güncelle' : 'Oluştur' }}</button>
            </div>
        </div>
    </form>
@endsection
