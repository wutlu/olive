@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Gerçek Zamanlı'
        ]
    ],
    'dock' => true
])

@push('local.styles')
    .groups > .collection-item {
        padding-right: 0;
        padding-left: 1rem;
    }
    .groups > .collection-item span.group-name {
        margin: 0 0 0 .5rem;
    }

    .time-line > .collection {
        overflow-y: scroll;
        height: 800px;
    }
@endpush

@section('content')
    <div class="card time-line">
        <div class="card-content card-content-image" style="background-image: url({{ asset('img/md-s/8.jpg') }});">
            <span class="card-title white-text mb-0">Veri Akışı</span>
        </div>
        <div class="card-content cyan darken-4 white-text">Pinlemek için verinin üzerine tıklayın.</div>
        <div class="collection">
            <a href="#" class="collection-item waves-effect">Örnek Tweet Mesela</a>
            <a href="#" class="collection-item waves-effect">Örnek Ekşi Mesela</a>
            <a href="#" class="collection-item waves-effect">Örnek Sahibinden Mesela</a>
            <a href="#" class="collection-item waves-effect">Örnek YouTube Mesela</a>
            <a href="#" class="collection-item waves-effect">Örnek Haber Mesela</a>
        </div>
    </div>
@endsection

@section('dock')
    <div class="card">
        <div class="card-content card-content-image" style="background-image: url({{ asset('img/md/8.jpg') }});">
            <span class="card-title white-text mb-0">Kelime Grupları</span>
        </div>
        <div class="card-image">
            <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-trigger="create">
                <i class="material-icons black-text">add</i>
            </a>
        </div>
        <div class="card-content white-text cyan darken-4">
            Takip etmek istediğiniz<br />grupları işaretleyin.<br />
            İşaretlemeler kaydedilmez.<br />Sayfa kapandığında<br />kaybolur.
        </div>
        <ul class="collection groups">
            <li class="collection-item d-block">
                <div class="d-flex justify-content-between">
                    <a class="material-icons" href="#">create</a>
                    <span class="group-name">test</span>
                    <div class="switch ml-auto">
                        <label>
                            <input type="checkbox" name="" id="" />
                            <span class="lever"></span>
                        </label>
                    </div>
                </div>
            </li>
            <li class="collection-item d-block">
                <div class="d-flex justify-content-between">
                    <a class="material-icons" href="#">create</a>
                    <span class="group-name">test</span>
                    <div class="switch ml-auto">
                        <label>
                            <input type="checkbox" name="" id="" />
                            <span class="lever"></span>
                        </label>
                    </div>
                </div>
            </li>
            <li class="collection-item d-block">
                <div class="d-flex justify-content-between">
                    <a class="material-icons" href="#">create</a>
                    <span class="group-name">test</span>
                    <div class="switch ml-auto">
                        <label>
                            <input type="checkbox" name="" id="" />
                            <span class="lever"></span>
                        </label>
                    </div>
                </div>
            </li>
            <a class="collection-item cyan darken-4 white-text waves-effect d-block" href="#">
                <span class="d-flex">
                    <span class="material-icons">history</span>
                    <span class="group-name">Pinleme Geçmişi</span>
                </span>
            </a>
        </ul>
    </div>
@endsection

@push('local.scripts')
    $(document).ready(function() {
        $('#keywords').characterCounter()
    }).on('click', '[data-trigger=check]', function() {
        var __ = $(this);

            __.children('input').prop('checked', true)
    })
@endpush
