@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Başlayın'
        ]
    ]
])

@push('local.scripts')
    $('.tabs').tabs();

    @if (session('timeout'))
        M.toast({ html: 'İşlem zaman aşımına uğradı! Lütfen tekrar deneyin.', classes: 'red' })
    @endif

    function __demo_request(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'Formunuzu Aldık!', classes: 'green darken-2' })
            M.toast({ html: 'Ekibimiz en kısa sürede sizinle iletişime geçecektir.', classes: 'blue-grey' })

            __.find('input[type=text]').html('')
        }
    }
@endpush

@section('content')
    <div class="card card-unstyled">
        <div class="card-content teal-text">
            @component('components.alert')
                @slot('icon', 'info')
                @slot('text', 'Bilgilerinizi bırakın, ekibimiz en uygun tekliflerle sizlere dönüş sağlasın.')
            @endcomponent
        </div>
        <div class="card-content">
            <form id="demo-form" method="post" action="{{ route('demo.request') }}" class="json d-table" data-callback="__demo_request">
                <div class="input-field">
                    <i class="material-icons prefix">account_circle</i>
                    <input id="icon_prefix" name="name" type="text" class="validate" />
                    <label for="icon_prefix">Firma / Kurum</label>
                </div>
                <div class="input-field">
                    <i class="material-icons prefix">phone</i>
                    <input id="icon_telephone" name="phone" type="text" class="validate" />
                    <label for="icon_telephone">Telefon</label>
                </div>
                <div class="input-field">
                    <div class="captcha" data-id="demo-captcha"></div>
                </div>
                <button type="submit" class="btn-flat waves-effect">Gönder</button>
            </form>
        </div>
    </div>
@endsection

@push('external.include.footer')
    <script src='//www.google.com/recaptcha/api.js'></script>
@endpush
