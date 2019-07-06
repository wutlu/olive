@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Partner'
        ],
        [
            'text' => 'Hesap Geçmişi'
        ]
    ],
    'footer_hide' => true,
    'dock' => true
])

@section('dock')
    @include('user.partner._menu', [ 'active' => 'account_history' ])
@endsection

@section('wildcard')
    <div class="card wild-background">
        <div class="container">
            <div class="d-flex">
                <img class="wildcard-icon" alt="{{ $user->partner }}" src="{{ asset('img/partner-'.$user->partner.'.png') }}" />
                <span class="wildcard-title">Hesap Geçmişi</span>
            </div>
        </div>
    </div>
@endsection

@section('action-bar')
    <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-tooltip="Ödeme İsteği" data-trigger="withdraw">
        <i class="material-icons grey-text text-darken-2">account_balance_wallet</i>
    </a>
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/jquery.maskedinput.min.js?v='.config('system.version')) }}"></script>
@endpush

@push('local.scripts')
    function __withdraw(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'İsteğiniz Alındı', classes: 'green' })

            $('#modal-withdraw').modal('close')

            setTimeout(function() {
                location.reload()
            }, 400)
        }
    }

    $(document).on('click', '[data-trigger=withdraw]', function() {
        var mdl = modal({
            'id': 'withdraw',
            'body': $('<form />', {
                'method': 'post',
                'action': '{{ route('partner.payment.request') }}',
                'id': 'form',
                'class': 'json',
                'data-callback': '__withdraw',
                'html': [
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<input />', {
                                'id': 'name',
                                'name': 'name',
                                'type': 'text',
                                'class': 'validate',
                                'placeholder': 'Alıcı'
                            }),
                            $('<span />', {
                                'class': 'helper-text',
                                'html': 'Alıcı adı veya ünvanı.'
                            })
                        ]
                    }),
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<input />', {
                                'id': 'iban',
                                'name': 'iban',
                                'type': 'text',
                                'class': 'validate',
                                'placeholder': 'IBAN'
                            }),
                            $('<span />', {
                                'class': 'helper-text',
                                'html': 'Alıcı IBAN numarası.'
                            })
                        ]
                    }),
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<span />', {
                                'class': 'prefix',
                                'html': '₺'
                            }),
                            $('<input />', {
                                'id': 'amount',
                                'name': 'amount',
                                'type': 'number',
                                'class': 'validate',
                                'value': 0,
                                'min': 0,
                                'placeholder': 'Miktar'
                            }),
                            $('<span />', {
                                'class': 'helper-text',
                                'html': 'Çekmek istediğiniz miktar.'
                            })
                        ]
                    }),
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<span />', {
                                'class': 'grey-text d-block',
                                'html': 'Vergiler düşüldükten sonra hesabınıza geçecek net rakam:'
                            }),
                            $('<span />', {
                                'class': 'green-text m-0',
                                'data-name': 'real_amount',
                                'html': 0
                            })
                        ]
                    })
                ]
            }),
            'size': 'modal-medium',
            'title': 'Ödeme İsteği',
            'options': {
                dismissible: false
            },
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect btn-flat grey-text',
                    'html': buttons.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<button />', {
                    'type': 'submit',
                    'class': 'waves-effect btn-flat',
                    'data-submit': 'form#form',
                    'html': buttons.ok
                })
            ]
        })

        M.updateTextFields()

        $('input[name=iban]').mask('TR99 9999 9999 9999 9999 9999 99')
        $('input[name=name]').focus()
    }).on('keydown keyup click change', 'input[name=amount]', function() {
        var __ = $(this);

        $('[data-name=real_amount]').html(__.val() - (__.val() / 100 * {{ config('formal.stoppage') }}))
    })
@endpush

@section('content')
    <div class="card">
        <div class="card-content">
            <span>
                <span class="grey-text d-block">Partner Bakiyesi</span>
                <span class="card-title green-text">{{ config('formal.currency') }} {{ $partner_wallet }}</span>
            </span>

            @if ($user->partnerPayments->count())
                <table class="highlight">
                    <tbody>
                        @foreach($user->partnerPayments()->paginate(10) as $invoice)
                            <tr>
                                <th>
                                    <p class="mb-0 grey-text">{{ $invoice->created_at }}</p>
                                    <p class="mb-0">{{ $invoice->message ? $invoice->message : '-' }}</p>
                                </th>

                                <th style="white-space: nowrap; vertical-align: top;" class="right-align {{ $invoice->process['color'] }}-text">
                                    <p class="mb-0">
                                        @if ($invoice->amount > 0){{ '+' }}@endif{{ $invoice->amount }}
                                        {{ $invoice->currency }}
                                    </p>
                                    <p class="mb-0">{{ $invoice->process['title'] }}</p>
                                </th>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {!! $user->partnerPayments()->paginate(10)->links('vendor.pagination.materializecss') !!}
            @else
                <div class="card-content">
                    @component('components.nothing')@endcomponent
                </div>
            @endif
        </div>
    </div>
@endsection
