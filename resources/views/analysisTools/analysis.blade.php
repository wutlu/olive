@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Analiz Araçları',
            'link' => route('analysis_tools.dashboard')
        ],
        [
            'text' => 'Form'
        ]
    ],
    'footer_hide' => true
])

@section('content')
    <div class="card card-unstyled">
        <ul class="collection collection-unstyled">
            <li class="collection-item">Analiz Araçları ile yapabileceğiniz işlemler Olive veri ekosisteminden bağımsızdır.</li>
            <li class="collection-item">Belirlediğiniz kanal veya profiller belirlediğiniz an itibariyle istatistiksel verileri için takibe alınır.</li>
            <li class="collection-item">Siz takibi bırakana kadar her gün yeni grafikler oluşturulur ve incelenmek üzere hazır hale getirilir.</li>
        </ul>
    </div>
@endsection
