@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Ayarlar'
        ],
        [
            'text' => 'Hesap Resmi'
        ]
    ],
    'dock' => true
])

@section('content')
<form method="post" action="{{ route('settings.avatar') }}" enctype="multipart/form-data">
	@csrf
	<div class="card">
	    <div class="card-image">
	        <img src="{{ asset('img/md-s/10.jpg') }}" alt="Hesap Resmi" />
	        <span class="card-title">Hesap Resmi</span>
	    </div>
		<div class="card-content">
			<div class="collection">
			    <div class="collection-item center-align">
			    	<img alt="Avatar" src="{{ auth()->user()->avatar() }}" style="max-width: 128px;" />
			    </div>
			    <div class="collection-item">
					<div class="file-field input-field">
						<div class="btn">
							<span>Dosya Seçin</span>
							<input type="file" name="file" id="file" accept="image/*" />
						</div>
						<div class="file-path-wrapper">
							<input class="file-path validate" type="text" />
						</div>
						@if ($errors->first('file'))
						<small class="helper-text red-text">{{ $errors->first('file') }}</small>
						@else
						<small class="helper-text">256x256 boyutunda bir resim seçin. Farklı boyuttaki resimler otomatik olarak 256x256 boyutunda boyutlandırılacaktır.</small>
						@endif
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
    @include('settings._menu', [ 'active' => 'avatar' ])
@endsection
