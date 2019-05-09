@extends('layouts.app', [
    'sidenav_layout' => true,
    'footer_hide' => true,
    'breadcrumb' => [
        [
            'text' => 'Arama Motoru'
        ]
    ],
    'wide' => true,
    'pin_group' => true
])

@push('local.styles')
    .marked {
        padding: .4rem;
        border-radius: .2rem;
    }

    #string {
        margin: 0;
        padding: 1rem;

                box-shadow: none !important;
        -webkit-box-shadow: none !important;

        border-width: 0;

        width: calc(100% - 2rem)
    }

    .owl-chart {
        position: relative;
    }
    .owl-chart .owl-wildcard-close {
        position: absolute;
        top: 0;
        right: 0;
    }
@endpush

@section('content')
    test
@endsection
