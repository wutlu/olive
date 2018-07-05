@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Başlayın'
        ]
    ]
])

@section('content')

Bir plan zaten mevcut.
<p>Ayrıl</p>
<p>Planı Sil</p>
<p>Yükselt</p>
<p>Devret</p>
<p>Uzat</p>

@endsection
