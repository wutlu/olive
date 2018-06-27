@extends('layouts.app')

@section('content')
<a href="{{ route('user.logout') }}">Çıkış</a>
@endsection

@push('local.scripts')

	@if (session('validate'))
	    M.toast({ html: 'Tebrikler! E-posta adresiniz doğrulandı!', classes: 'green' })
	@endif

@endpush
