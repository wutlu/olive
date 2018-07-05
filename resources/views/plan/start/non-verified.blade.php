@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Başlayın'
        ]
    ]
])

@section('content')
	<div class="rush-alert">
        <i class="material-icons">email</i>
        <h5>Onay Gerekiyor :(</h5>
        <p>Organizasyon oluşturabilmek için e-posta adresinizi doğrulamanız gerekiyor.</p>
    </div>
@endsection
