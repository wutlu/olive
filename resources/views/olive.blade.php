@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'report_menu' => true,
    'chat' => true
])

@section('content')
    <div class="olive-alert warning m-1">
        <div class="anim"></div>

        <h4 class="mb-1">Uyarı</h4>

        <p class="mb-1">Olive alt alanadını artık kullanmıyoruz. Lütfen VERİ.ZONE ana sayfasından gitmek istediğiniz sayfaya yönelin.</p>

        <a href="https://veri.zone/" class="btn-flat waves-effect">VERİ.ZONE</a>
    </div>
@endsection
