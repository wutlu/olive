@extends('layouts.app', [ 'header' => 'hide' ])

@section('title', 'Yeni Şifreniz')

@section('content')
    <header id="main">
        <div class="parallax-container">
            <div class="parallax indigo lighten-5">
                <img src="{{ asset('img/bg-2.svg') }}" alt="bg-1" />
            </div>

            <div class="container">
                <div class="card" style="max-width: 260px;">
                	<div class="card-image" style="padding: 1rem 2rem 0;">
    	            	<img src="{{ asset('img/olive-logo.svg') }}" alt="olive-logo" class="responsive-img" />
    	            </div>
                    <div class="card-content">
                        <p>Lütfen yeni şifrenizi oluşturun.</p>
                        <form id="password-form" data-callback="__password" action="{{ route('user.password.new', [ 'id' => $user->id, 'sid' => $user->session_id ]) }}" method="patch" class="json">
                            <div class="row">
                                <div class="input-field col s12">
                                    <input name="email" id="email" type="email" class="validate" />
                                    <label for="email">E-posta</label>
                                    <span class="helper-text">Geçerli e-posta adresiniz.</span>
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
                                <div class="right-align">
                                    <button type="submit" class="waves-effect waves-light btn cyan darken-4">Güncelle</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>
@endsection

@push('local.scripts')
    function __password(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'Şifreniz güncellendi.', classes: 'green' })

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
