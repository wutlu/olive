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
<div class="card">
    <div class="card-image">
        <img src="{{ asset('img/md-s/10.jpg') }}" alt="E-posta Bildirimleri" />
        <span class="card-title">E-posta Bildirimleri</span>
    </div>
	<div class="card-content">
		<div class="collection">
	        @foreach(config('app.notifications') as $key => $name)
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
</div>
@endsection

@section('dock')
    @include('settings._menu', [ 'active' => 'notifications' ])
@endsection
