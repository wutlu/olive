@component('mail::message')
# {{ $data['alarm']->search->name }}

_Alarm değerleri son {{ $data['alarm']->interval }} dakika içerisindeki aksiyonlardan elde edilmiştir._

{{ $data['sources'] }}

@if (count($data['data']))
## Başlıca İçerikler

@foreach ($data['data'] as $item)
- {!! $item !!}
@endforeach
@endif

## Twitter & Instagram - Etkileşim
{{ $data['stats'] }}

@component('mail::button', ['url' => route('search.dashboard', [
    'q' => $data['alarm']->search->string,
    's' => date('Y-m-d'),
    'e' => date('Y-m-d')
]), 'color' => 'green']){{ 'Arama Motoru ile Aç' }}@endcomponent

> ** Alarm Bilgisi **

> {{
    implode(
        ', ',
        array_map(
            function($item)
            {
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
            },
            $data['alarm']->weekdays
        )
    )
}}

> **{{ $data['alarm']->start_time }}** - **{{ $data['alarm']->end_time }}** saatleri arasında, her **{{ $data['alarm']->interval }}** dakikada bir bildirim alacaksınız. Kalan **{{ $data['alarm']->hit }}** bildiriminiz daha var.

@endcomponent
