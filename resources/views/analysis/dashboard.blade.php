@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Kelime Hafızası'
        ]
    ]
])

@section('wildcard')
    <div class="z-depth-1">
        <div class="container">
            <div class="pt-1 pb-1 grey-text text-darken-2">
                @component('components.alert')
                    @slot('text', 'Bu sayfa sadece yetkili kullanıcılar tarafından görüntülenebilir.')
                    @slot('icon', 'warning')
                @endcomponent
            </div>
        </div>
    </div>
@endsection

@push('local.scripts')
    function __status_set(__, obj)
    {
        if (obj.status == 'err')
        {
            M.toast({ html: 'İlgili değer option tablosunda bulunamadı.', classes: 'red' })

            __.prop('checked', false)
        }
    }
@endpush

@section('content')
    <div class="fast-menu">
        @foreach ($modules as $key => $item)
            <a href="{{ route('analysis.module', $key) }}">
                <div class="plane">
                    <div class="circle"></div>
                    <div class="circle"></div>
                    <div class="circle"></div>
                    <div class="circle"></div>
                    <div class="circle"></div>
                    <div class="circle"></div>
                </div>
                <span class="d-block">{{ $item['title'] }}</span>
                <ul class="collection"> 
                    @foreach ($item['types'] as $tkey => $type)
                        <li class="collection-item d-flex justify-content-between">
                            <span>{{ $type['title'] }}</span>
                            <span>{{ $data[$tkey]['new'].'/'.$data[$tkey]['compiled'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </a>
        @endforeach
    </div>
@endsection

@section('subcard')
    <div class="grey lighten-4 p-1">
        <div class="container">
            <label>
                <input
                    name="value"
                    id="value"
                    value="on"
                    class="json"
                    data-href="{{ route('analysis.learn') }}"
                    data-method="patch"
                    data-delay="1"
                    data-key="data.learn"
                    data-checked-value="on"
                    data-unchecked-value="off"
                    type="checkbox"
                    data-callback="__status_set"
                    @if ($learn == 'on'){{ 'checked' }}@endif />
                <span class="teal-text">Makine öğrenmesini aktif et.</span>
            </label>
            <p class="teal-text mt-1">Bu alan aktif edilirse, sistem zaman zaman her kategori için yeni kelimeler tahmin edecektir. Edilen tahminler derlenmek üzere modaratör onayında bekletilecektir.</p>
        </div>
    </div>
@endsection
