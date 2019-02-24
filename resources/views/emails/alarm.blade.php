@component('mail::message')
# {{ $data['alarm']->name }}

Son {{ $data['alarm']->interval }} dakika içerisinde alınan değerler.

{{ $data['sources'] }}

@if (count($data['data']))
## Başlıca İçerikler

@foreach ($data['data'] as $item)
- {{ $item }}
@endforeach
@endif

@component('mail::button', ['url' => route('search.dashboard', [
    'q' => $data['alarm']->query,
    's' => date('Y-m-d'),
    'e' => date('Y-m-d')
]), 'color' => 'green']){{ 'Tüm İçerikler' }}@endcomponent

Geciken içeriklerden dolayı, Olive üzerinde sorgulayacağınız rakamlar bu rakamlardan daha yüksek değerler gösterebilir.

> ** Alarm Bilgisi **

> {{ implode(', ', array_map(function($item) {
    return str_replace(
        [
            'day_1',
            'day_2',
            'day_3',
            'day_4',
            'day_5',
            'day_6',
            'day_7',
        ],
        [
            'Pazartesi',
            'Salı',
            'Çarşamba',
            'Perşembe',
            'Cuma',
            'Cumartesi',
            'Pazar'
        ],
        $item
    );
}, $data['alarm']->weekdays)) }}

> **{{ $data['alarm']->start_time }}** - **{{ $data['alarm']->end_time }}** saatleri arasında, her **{{ $data['alarm']->interval }}** dakikada bir bildirim alacaksınız. Kalan **{{ $data['alarm']->hit }}** bildiriminiz daha var.

@endcomponent
