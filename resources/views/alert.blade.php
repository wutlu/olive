@extends('layouts.app', [
    'breadcrumb' => [
        [
            'text' => @$data->title ? $data->title : 'Uyarı'
        ]
    ],
    'footer_hide' => true,
    'robots' => [ 'noindex' ]
])

@section('content')
    <div class="olive-alert {{ @$data->status ? $data->status : 'warning' }} m-1">
        <div class="anim"></div>
        <h4 class="mb-1">{{ @$data->title ? $data->title : 'Uyarı' }}</h4>

        @isset ($data->message)
            <p class="mb-1">{!! $data->message !!}</p>
        @endisset

        @isset ($data->button)
            <a href="{{ $data->button['route'] }}" class="btn-flat waves-effect">{{ $data->button['text'] }}</a>
        @else
            <a href="{{ route('dashboard') }}" class="btn-flat waves-effect">Ana Sayfa</a>
        @endisset
    </div>
@endsection
