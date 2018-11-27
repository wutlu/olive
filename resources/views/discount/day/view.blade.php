@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'İndirim Günleri',
            'link' => route('admin.discount.day.list')
        ],
        [
            'text' => @$day ? 'İndirim Günü' : 'İndirim Günü Oluştur'
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
                M.toast({ html: 'İndirim Günü Güncellendi', classes: 'green darken-2' })
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
                        'data-href': '{{ route('admin.discount.day') }}',
                        'data-method': 'delete',
                        'data-callback': '__delete'
                    })
               ])
    })

    function __delete(__, obj)
    {
        if (obj.status == 'ok')
        {
            location.href = '{{ route('admin.discount.day.list') }}';
        }
    }

    @if (session('status') == 'created')
        M.toast({ html: 'İndirim Günü Oluşturuldu', classes: 'green darken-2' })
    @endif
@endpush

@section('content')
    <form method="{{ @$day ? 'patch' : 'put' }}" action="{{ route('admin.discount.day') }}" class="json" id="details-form" data-callback="__form">
        @if (@$day)
        <input type="hidden" value="{{ $day->id }}" name="id" id="id" />
        @endif
        <div class="card">
            <div class="card-image">
                <img src="{{ asset('img/card-header.jpg') }}" alt="{{ @$day ? 'İndirim Günü' : 'İndirim Günü Oluştur' }}" />
                <span class="card-title">{{ @$day ? 'İndirim Günü' : 'İndirim Günü Oluştur' }}</span>
            </div>
            <div class="card-content">
                <div class="collection">
                    <div class="collection-item">
                        <div class="input-field" style="max-width: 240px;">
                            <input name="first_day" id="first_day" value="{{ @$day->first_day }}" type="date" class="validate" />
                            <label for="first_day">Başlangıç Günü</label>
                            <small class="helper-text">İndirim kampanyasının başladığı gün.</small>
                        </div>
                    </div>
                    <div class="collection-item">
                        <div class="input-field" style="max-width: 240px;">
                            <input name="last_day" id="last_day" value="{{ @$day->last_day }}" type="date" class="validate" />
                            <label for="last_day">Bitiş Günü</label>
                            <small class="helper-text">İndirim kampanyasının biteceği gün.</small>
                        </div>
                    </div>
                    <div class="collection-item">
                        <div class="input-field" style="max-width: 240px;">
                            <input name="discount_rate" id="discount_rate" value="{{ @$day->discount_rate }}" type="number" max="100" min="0" class="validate" />
                            <label for="discount_rate">İndirim Oranı (%)</label>
                            <small class="helper-text">Kuponun sağlayacağı indirim oranı.</small>
                        </div>
                    </div>
                    <div class="collection-item">
                        <div class="input-field" style="max-width: 240px;">
                            <input name="discount_price" id="discount_price" value="{{ @$day->discount_price }}" type="number" min="0" class="validate" />
                            <label for="discount_price">İndirim Miktarı ({{ config('formal.currency') }})</label>
                            <small class="helper-text">Kuponun sağlayacağı indirim miktarı.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-action right-align">
                @if (@$day)
                    <a href="#" class="btn-flat waves-effect" data-trigger="delete">Sil</a>
                @endif
                <button type="submit" class="btn waves-effect">{{ @$day ? 'Güncelle' : 'Oluştur' }}</button>
            </div>
        </div>
    </form>
@endsection
