@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Ayarlar'
        ],
        [
            'text' => 'Hesap Bilgileri'
        ]
    ],
    'dock' => true
])

@push('local.scripts')
    function __account(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('input[name=password]').val('')

            M.toast({
                html: 'Hesap Bilgileri Güncellendi',
                classes: 'green'
            })
        }
    }
@endpush

@section('content')
	<form method="post" action="{{ route('settings.account') }}" class="json" id="details-form" data-callback="__account">
		<div class="card">
		    <div class="card-image">
		        <img src="{{ asset('img/md-s/10.jpg') }}" alt="Hesap Bilgileri" />
		        <span class="card-title">Hesap Bilgileri</span>
		    </div>
			<div class="card-content">
				<div class="collection">
				    <div class="collection-item">
				        <div class="input-field" style="max-width: 240px;">
				            <input name="name" id="name" value="{{ $user->name }}" type="text" class="validate" />
				            <label for="name">Ad</label>
				            <small class="helper-text">Sistem üzerinde görünen kullanıcı adınız.</small>
				        </div>
				    </div>
				    <div class="collection-item">
				        <div class="input-field" style="max-width: 240px;">
				            <input name="email" id="email" value="{{ $user->email }}" type="email" class="validate" />
				            <label for="email">E-posta</label>
				            <small class="helper-text">Sistemdeki e-posta adresiniz.</small>
				        </div>
				    </div>
				    <hr />
				    <div class="collection-item">
				        <div class="input-field" style="max-width: 240px;">
				            <input name="password" id="password" type="password" class="validate" />
				            <label for="password">Şifre</label>
				            <small class="helper-text">Değiştirmek istemiyorsanız boş bırakın.</small>
				        </div>
				    </div>
				</div>
			</div>
			<div class="card-action right-align">
				<button type="submit" class="btn-flat waves-effect">Güncelle</button>
			</div>
		</div>
	</form>
@endsection

@section('dock')
    @include('settings._menu', [ 'active' => 'account' ])
@endsection