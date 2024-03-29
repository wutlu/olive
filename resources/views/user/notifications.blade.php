@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Ayarlar'
        ],
        [
            'text' => 'E-posta Bildirimleri'
        ]
    ],
    'dock' => true
])

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">E-posta Bildirimleri</span>
        </div>
        <div class="collection collection-unstyled">
            @foreach(config('system.notifications') as $key => $name)
            <label class="collection-item waves-effect d-block">
                <input
                    name="key"
                    id="notification-{{ $key }}"
                    value="{{ $key }}"
                    class="json"
                    data-href="{{ route('settings.notification') }}"
                    data-method="patch"
                    data-delay="1"
                    type="checkbox"
                    {{ auth()->user()->notification($key) ? 'checked' : '' }} />
                <span>{{ $name }}</span>
            </label>
            @endforeach
        </div>
    </div>
@endsection

@section('dock')
    @include('settings._menu', [ 'active' => 'notifications' ])
@endsection
