@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Başlayın'
        ]
    ]
])

@push('local.scripts')
    $('.tabs').tabs();

    @if (session('timeout'))
        M.toast({ html: 'İşlem zaman aşımına uğradı! Lütfen tekrar deneyin.', classes: 'red' })
    @endif
@endpush

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/md-s/21.jpg') }}" alt="Image" />
            <span class="card-title white-text d-flex">
                <i class="material-icons align-self-center mr-1">record_voice_over</i>
                Başlayın
            </span>
        </div>
        <div class="card-content">
            <div class="p-1 center">
                <p class="teal-text">Yeni bir organizasyon oluşturabilmek için lütfen paket teklifi isteyin.</p>
                <p class="grey-text">Destek sistemimize mesaj bırakın. Uzman ekibimizle en kısa sürede en iyi tekliflerle size dönüş yapalım..</p>

                <div class="d-table mx-auto mt-1">
                    <a href="{{ route('settings.support', 'organisayon-teklifi') }}" class="btn-flat waves-effect d-flex">
                        <i class="material-icons mr-1">record_voice_over</i>
                        Teklif İsteyin
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
