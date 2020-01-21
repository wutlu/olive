@extends('layouts.app', [
    'breadcrumb' => [
        [
            'text' => 'CRM'
        ]
    ],
    'sidenav_fixed_layout' => true,
    'footer_hide' => true
])

@section('content')
    <div class="item-group">
        @foreach ($tusers as $tuser)
            <span class="item d-flex {{ $tuser->status ? ($tuser->verified ? 'blue lighten-5' : '') : 'red' }} lighten-5">
                <img alt="Avatar" src="{{ $tuser->avatar }}" class="circle align-self-center" style="width: 32px; height: 32px;" />
                <div class="align-self-center flex-fill pl-1">
                    <p class="mb-0">{{ $tuser->name }}</p>
                    <p class="mb-0 grey-text">{{ '@'.$tuser->nickname }}</p>
                </div>
                <span class="align-self-start right-align">
                    <a href="{{ route('provider.drop', [ 'provider' => 'twitter', 'id' => $tuser->id ]) }}" class="d-block">
                        <i class="material-icons">close</i>
                    </a>
                </span>
            </span>
        @endforeach
        @for ($i = 0; $i <= (3-count($tusers)); $i++)
            <a href="{{ route('provider.redirect', 'twitter') }}" class="item grey-text center-align d-flex justify-content-center">
                <span class="align-self-center">
                    <i class="social-icon icon-twitter mb-1">&#xe803;</i>
                    <span class="d-block">Hesap Bağla</span>
                </span>
            </a>
        @endfor
    </div>
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/md-s/29.jpg') }}" alt="Image" />
            <span class="card-title">Planlanmış Gönderiler</span>
            <a href="#" class="btn-floating halfway-fab waves-effect white" data-trigger="create-cat" data-tooltip="Gönderi Planla">
                <i class="material-icons grey-text text-darken-2">add</i>
            </a>
        </div>
        <ul class="collection collection-unstyled">
            <li class="collection-item">
                <div class="d-flex justify-content-between p-1">
                    <div class="align-self-center">
                        <p class="mb-0">EGM Konusu</p>
                        <p class="mb-0"><span class="grey-text">@alper_toksoz</span> Hesabından</p>
                    </div>
                    <div class="align-self-center">
                        Cevaplandı <span class="badge grey white-text">14</span>
                    </div>
                </div>
            </li>
        </ul>
    </div> 
@endsection

@push('local.styles')
    .item-group {
        padding: 16px 16px 0;
    }
    .item-group > .item {
        border-style: dashed;
        border-color: #ccc;
        border-width: 2px 2px 0;
        border-radius: 16px 16px 0 0;
        padding: 16px;
    }
    .item-group > a.item:hover {
        background-color: #f0f0f0;
    }
    
    @media (max-width: 1366px) {
        .item-group {
            padding: 4px;
        }
        .item-group > .item {
            border-radius: 0;
            border-width: 0;
        }
    }
@endpush
