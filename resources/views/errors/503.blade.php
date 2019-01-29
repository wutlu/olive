@extends('errors::illustrated-layout')

@section('code', '503')
@section('title', __('Bakım Modu'))

@section('image')
<div style="background-image: url({{ asset('/svg/503.svg') }});" class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center">
</div>
@endsection

@section('message', __($exception->getMessage() ?: 'Yedekleme ve bakım için ara verdik. En geç 10 dakika içerisinde tekrar aktif olacağız.'))
