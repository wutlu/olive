@extends('errors::illustrated-layout')

@section('code', '419')
@section('title', __('Sayfa Süresi Doldu!'))

@section('image')
<div style="background-image: url({{ asset('/svg/403.svg') }});" class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center">
</div>
@endsection

@section('message', __('Üzgünüz, oturumunuzun süresi doldu. Lütfen sayfayı yenileyin ve tekrar deneyin.
'))
