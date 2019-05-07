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
