@extends('errors::illustrated-layout')

@section('code', '503')
@section('title', __('Servis Modu'))

@section('message', __($exception->getMessage() ?: 'Bakım ve yedekleme zamanı! Sistem kısa bir süre sonra aktif olacak.'))

@section('content')
    <div id="pacman"></div>
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/modernizr.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/jquery.min.js?v='.config('system.version')) }}"></script>
    <script src="{{ asset('js/pacman.min.js?v='.config('system.version')) }}"></script>
@endpush

@push('local.styles')
    #pacman {
        height: 470px;
        width: 382px;
        margin: 10vh auto;
    }

    body {
        background-color: #000;
    }

    @media (max-width: 1024px)
    {
        #pacman {
            display: none;
        }

        body {
            background-color: #fff;
        }
    }
@endpush
