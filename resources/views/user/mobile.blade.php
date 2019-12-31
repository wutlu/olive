@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Ayarlar'
        ],
        [
            'text' => 'Mobil'
        ]
    ],
    'dock' => true
])

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Mobil</span>
            <div class="p-1">
                @if ($user->gsm)
                    <small class="{{ $user->gsm_verified_at ? 'green-text' : 'red-text' }}">{{ $user->gsm_verified_at ? 'Doğrulandı' : 'Doğrulama Bekliyor' }}</small>
                    <div class="d-flex">
                        <h6 class="m-0 align-self-center {{ $user->gsm_verified_at ? 'green-text' : 'red-text' }}">{{ $user->gsm }}</h6>
                        <a href="#" class="btn-flat btn-floating waves-effect align-self-center ml-1" data-trigger="delete">
                            <i class="material-icons">delete</i>
                        </a>
                        @if (!$user->gsm_verified_at)
                            <a
                                href="#"
                                class="btn-flat btn-floating waves-effect align-self-center ml-1 json"
                                data-tooltip="Tekrar Gönder"
                                data-href="{{ route('settings.mobile.resend') }}"
                                data-callback="__resend"
                                data-method="post">
                                <i class="material-icons">refresh</i>
                            </a>
                            @push('local.scripts')
                                function __resend(__, obj)
                                {
                                    if (obj.status == 'ok')
                                    {
                                        M.toast({ html: 'Doğrulama kodu tekrar gönderildi!', 'classes': 'green darken-2' })
                                    }
                                }
                            @endpush
                        @endif
                    </div>

                    @push('local.scripts')
                        $(document).on('click', '[data-trigger=delete]', function() {
                            return modal({
                                'id': 'alert',
                                'body': 'Bu numarayı kaldırmak istediğinizden emin misiniz?',
                                'size': 'modal-small',
                                'title': 'Sil',
                                'footer': [
                                    $('<a />', {
                                        'href': '#',
                                        'class': 'modal-close waves-effect grey-text btn-flat',
                                        'html': keywords.cancel
                                    }),
                                    $('<span />', {
                                        'html': ' '
                                    }),
                                    $('<a />', {
                                        'href': '#',
                                        'class': 'waves-effect btn-flat red-text json',
                                        'html': keywords.ok,
                                        'data-href': '{{ route('settings.mobile.delete') }}',
                                        'data-method': 'delete',
                                        'data-callback': '__delete'
                                    })
                                ],
                                'options': {}
                            })
                        })

                        function __delete(__, obj)
                        {
                            if (obj.status == 'ok')
                            {
                                M.toast({ html: 'GSM numarası kaldırıldı!', 'classes': 'cyan darken-2' })

                                setTimeout(function() {
                                    location.reload()
                                }, 1000)
                            }
                        }
                    @endpush

                    @if (!$user->gsm_verified_at)
                        <form method="patch" action="{{ route('settings.mobile.verification') }}" class="json" id="mobile-form" data-callback="__mobile">
                            <div class="input-field" style="max-width: 120px;">
                                <input name="code" id="code" type="text" class="validate" autocomplete="off" />
                                <label for="code">Kod</label>
                                <small class="helper-text">Doğrulama kodunu girin.</small>
                            </div>
                            <button type="submit" class="btn-flat waves-effect">Doğrula</button>
                        </form>
                        @push('local.scripts')
                            $('input[name=code]').focus()

                            function __mobile(__, obj)
                            {
                                if (obj.status == 'ok')
                                {
                                    flash_alert('Doğrulama Başarılı!', 'green white-text')

                                    setTimeout(function() {
                                        if (obj.demo == true)
                                        {
                                            location.href = '{{ route('search.dashboard') }}';
                                        }
                                        else
                                        {
                                            location.reload()
                                        }
                                    }, 1200)
                                }
                            }
                        @endpush
                        <div class="grey-text text-darken-2 mt-1">
                            @component('components.alert')
                                @slot('icon', 'info')
                                @slot('text', '10 dakika içerisinde doğrulama SMS\'i gelmediyse Tekrar Gönder butonunu kullanın. GSM numaranızı yanlış girdiğinizi düşünüyorsanız silin ve tekrar ekleyin.')
                            @endcomponent
                        </div>
                    @endif
                @else
                    <form method="put" action="{{ route('settings.mobile.create') }}" class="json" id="mobile-form" data-callback="__mobile">
                        <div class="input-field" style="max-width: 240px;">
                            <input name="gsm" id="gsm" type="text" class="validate" />
                            <label for="gsm">GSM</label>
                            <small class="helper-text">GSM numaranızı girin.</small>
                        </div>
                        <button type="submit" class="btn-flat waves-effect">Ekle</button>
                    </form>
                    @push('local.scripts')
                        var gsm = $('input#gsm');
                            gsm.focus()
                        var focusDriver = new Driver();
                            focusDriver.highlight({
                                element: '#' + gsm.attr('id'),
                                popover: {
                                    title: 'GSM Ekleyin',
                                    description: '{{ $user->organisation_id ? 'Size daha iyi hizmet verebilmemiz için lütfen bir GSM numaranızı bırakın.' : 'Numaranızı ekledikten sonra Veri Zone\'u hemen denemeye başlayabilirsiniz.' }}',
                                }
                            })

                        $(document).on('keyup', gsm, function() {
                            focusDriver.reset()
                        })

                        function __mobile(__, obj)
                        {
                            if (obj.status == 'ok')
                            {
                                location.reload()
                            }
                        }
                    @endpush
                @endif
            </div>
        </div>
    </div>
@endsection

@section('dock')
    @include('settings._menu', [ 'active' => 'mobile' ])
@endsection

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/driver.min.css?v='.config('system.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/jquery.maskedinput.min.js?v='.config('system.version')) }}"></script>
    <script>
        $('input#gsm').mask('(999) 999 99 99')
        $('input#code').mask('9999')
    </script>
    <script src="{{ asset('js/driver.min.js?v='.config('system.version')) }}"></script>
@endpush
