@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Veri Havuzu'
        ]
    ],
    'dock' => true,
    'footer_hide' => true
])

@section('content')
    <div class="card card-unstyled">
        <div class="card-content">
            <span class="card-title d-flex">
                <i class="material-icons mr-1">filter_list</i>
                Veri Havuzu
            </span>
        </div>
        <ul class="collection collection-unstyled">
            <li class="collection-item">Olive ekosistemi, ortak veri havuzu prensibiyle çalışır.</li>
            <li class="collection-item">Olive organizasyonlarının ve Olive ekibinin belirlediği kriterlere göre, sosyal medya verileri analiz edilmek üzere toplanır.</li>
            <li class="collection-item">Elde edilen veriler; anlık veri sorgulama motorları ile, kullanıcı ekranlarına sunulur.</li>
            <li class="collection-item">Havuz kriterlerini, organizasyonunuza ait limitler doğrultusunda kullanabilirsiniz.</li>
        </ul>
    </div>
@endsection

@section('dock')
	@include('dataPool._menu', [ 'active' => 'dashboard' ])
@endsection
