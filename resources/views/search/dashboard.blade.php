@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Arama'
        ]
    ],
    'dock' => true
])

@section('content')
    <div class="card card-unstyled">
        <div class="card-content">
            <div class="input-field">
                <input name="string" id="string" type="text" class="validate" />
                <label for="string">Ara</label>
                <span class="helper-text d-flex">
                    <a href="#" class="align-self-center" data-trigger="info" style="margin: 0 .4rem 0 0;">
                        <i class="material-icons">info_outline</i>
                    </a>
                    <span class="align-self-center">Aramak istediğiniz kelimeyi veya kriteri girin.</span>
                </span>
            </div>
        </div>
    </div>
@endsection

@include('_inc.alerts.search_operators')

@push('external.include.footer')
    <script src="{{ asset('js/jquery.mark.min.js?v='.config('system.version')) }}" charset="UTF-8"></script>
@endpush

@section('wildcard')
    <div class="z-depth-1">
        <div class="container wild-area">
            <div class="wild-content d-flex" data-wild="date">
                <span class="wild-body d-flex">
                    <input style="max-width: 96px;" type="text" class="datepicker" value="{{ date('Y-m-d', strtotime('-1 day')) }}" placeholder="Başlangıç" />
                    <input style="max-width: 96px;" type="text" class="datepicker" value="{{ date('Y-m-d') }}" placeholder="Bitiş" />
                </span>
            </div>
            <ul class="wild-menu">
                <li>
                    <a class="d-flex" href="#" data-class="[data-wild=date]" data-class-add="active">
                        <i class="material-icons mr-1">date_range</i>
                        <span class="align-self-center">Tarih Aralığı</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
@endsection

@section('dock')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Kaynak</span>
        </div>
        <div class="collection collection-bordered">
            <label class="collection-item waves-effect d-block">
                <input name="sound_alert" value="on" type="checkbox" />
                <span>Kelimesi Kelimesine</span>
            </label>
            <div class="divider"></div>
            @foreach (config('system.modules') as $key => $module)
                <label class="collection-item waves-effect d-block">
                    <input name="sound_alert" checked value="on" type="checkbox" />
                    <span>{{ $module }}</span>
                </label>
            @endforeach
        </div>
    </div>
@endsection

@push('local.scripts')
    $('.datepicker').datepicker({
        firstDay: 0,
        format: 'yyyy-mm-dd',
        i18n: date.i18n,
        container: 'body'
    })
@endpush
