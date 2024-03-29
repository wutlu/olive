@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Kullanıcılar',
            'link' => route('admin.user.list')
        ],
        [
            'text' => $user->name,
            'link' => route('admin.user', $user->id)
        ],
        [
            'text' => '🐞 E-posta Bildirimleri'
        ]
    ],
    'dock' => true,
    'footer_hide' => true
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
                        data-href="{{ route('admin.user.notification', $user->id) }}"
                        data-method="patch"
                        data-delay="1"
                        type="checkbox"
                        {{ $user->notification($key) ? 'checked' : '' }} />
                    <span>{{ $name }}</span>
                </label>
            @endforeach
        </div>
    </div>
@endsection

@section('dock')
    @include('user.admin._menu', [ 'active' => 'notifications', 'id' => $user->id ])
@endsection
