@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Başlayın'
        ]
    ]
])

@if (session('organisation') == 'have')
	@push('local.scripts')
		M.toast({
            html: 'Zaten bir organizasyonunuz mevcut.',
            classes: 'blue'
        })
	@endpush
@endif

@section('content')

Bir plan zaten mevcut.
<p>Ayrıl</p>
<p>Planı Sil</p>
<p>Yükselt</p>
<p>Devret</p>
<p>Uzat</p>

@endsection
