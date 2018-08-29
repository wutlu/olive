@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'İndirim Kuponları',
            'link' => route('admin.discount.coupon.list')
        ],
        [
            'text' => @$coupon ? $coupon->key : 'Kupon Oluştur'
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
                M.toast({ html: 'Kupon Güncellendi', classes: 'green' })
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
                        'data-href': '{{ route('admin.discount.coupon') }}',
                        'data-method': 'delete',
                        'data-callback': '__delete'
                    })
               ])
    })

    function __delete(__, obj)
    {
        if (obj.status == 'ok')
        {
            location.href = '{{ route('admin.discount.coupon.list') }}';
        }
    }

    @if (session('status') == 'created')
        M.toast({ html: 'Kupon Oluşturuldu', classes: 'green' })
    @endif
@endpush

@section('content')
    <form method="{{ @$coupon ? 'patch' : 'put' }}" action="{{ route('admin.discount.coupon') }}" class="json" id="details-form" data-callback="__form">
        @if (@$coupon)
        <input type="hidden" value="{{ $coupon->id }}" name="id" id="id" />
        @endif
        <div class="card">
            <div class="card-image">
                <img src="{{ asset('img/md-s/19.jpg') }}" alt="{{ @$coupon ? $coupon->key : 'Kupon Oluştur' }}" />
                <span class="card-title">{{ @$coupon ? $coupon->key : 'Kupon Oluştur' }}</span>
            </div>
            <div class="card-content">
                <div class="collection">
                    <div class="collection-item">
                        <div class="input-field" style="max-width: 240px;">
                            <input name="key" id="key" value="{{ @$coupon->key }}" type="text" class="validate" />
                            <label for="key">Kupon Kodu</label>
                            <small class="helper-text">Benzersiz bir kupon kodu girin.</small>
                        </div>
                    </div>
                    <div class="collection-item">
                        <div class="input-field" style="max-width: 240px;">
                            <input name="rate" id="rate" value="{{ @$coupon->rate }}" type="number" max="100" min="0" class="validate" />
                            <label for="rate">İndirim Oranı (%)</label>
                            <small class="helper-text">Kuponun sağlayacağı indirim oranı.</small>
                        </div>
                    </div>
                    <div class="collection-item">
                        <div class="input-field" style="max-width: 240px;">
                            <input name="price" id="price" value="{{ @$coupon->price }}" type="number" min="0" class="validate" />
                            <label for="price">İndirim Miktarı ({{ config('formal.currency') }})</label>
                            <small class="helper-text">Kuponun sağlayacağı indirim miktarı.</small>
                        </div>
                    </div>
                    <div class="collection-item">
                        <div class="input-field" style="max-width: 240px;">
                            <input name="count" id="count" value="{{ @$coupon->count }}" type="number" min="1" class="validate" />
                            <label for="count">Adet</label>
                            <small class="helper-text">Kupon sayısı.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-action right-align">
                @if (@$coupon)
                    <a href="#" class="btn-flat waves-effect" data-trigger="delete">Sil</a>
                @endif
                <button type="submit" class="btn waves-effect">{{ @$coupon ? 'Güncelle' : 'Oluştur' }}</button>
            </div>
        </div>
    </form>
@endsection
