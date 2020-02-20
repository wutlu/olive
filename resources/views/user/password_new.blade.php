@extends('layouts.app', [
    'term' => 'hide',
    'email' => 'hide',
    'dock' => true
])

@section('title', 'Yeni Şifreniz')

@section('content')
    <header id="main">
        <div class="container">
            <div class="pt-2 pb-2 mt-2 mb-2">
                <form id="password-form" data-callback="__password" action="{{ route('user.password.new', [ 'id' => $user->id, 'sid' => $user->session_id ]) }}" method="patch" class="json">
                    <div class="card" style="max-width: 460px;">
                        <div class="card-content">
                            <img src="{{ asset('img/8vz.net_logo.svg') }}" alt="8vz.net-logo" class="responsive-img" style="width: 128px;" />
                        </div>
                        <div class="card-content">
                            <div class="row">
                                <div class="input-field col s12">
                                    <input name="email" id="email" type="email" class="validate" />
                                    <label for="email">E-posta</label>
                                    <span class="helper-text">Mevcut e-posta adresiniz.</span>
                                </div>
                                <div class="input-field col s12">
                                    <input name="password" id="password" type="password" class="validate" />
                                    <label for="password">Yeni Şifre</label>
                                    <span class="helper-text">Yeni şifreniz.</span>
                                </div>
                                <div class="input-field col s12">
                                    <input name="password_confirmation" id="password_confirmation" type="password" class="validate" />
                                    <label for="password_confirmation">Yeni Şifre</label>
                                    <span class="helper-text">Yeni şifreniz.</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-content teal lighten-5 teal-text">Yeni şifrenizi oluşturun.</div>
                        <div class="card-action right-align">
                            <button type="submit" class="btn-flat waves-effect">Güncelle</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </header>
@endsection

@push('local.scripts')
    function __password(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'Şifreniz güncellendi.', classes: 'green darken-2' })

            setTimeout(goHome, 1000)
        }
    }

    function goHome()
    {
        location.href = '{{ route('home') }}';
    }

	$(document).ready(function() {
        $('.parallax').parallax();
    });
@endpush
