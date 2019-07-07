@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Muhasebe'
        ],
        [
            'text' => '🐞 Partner Ödemeleri'
        ]
    ],
    'footer_hide' => true,
    'dock' => true
])

@section('dock')
    @include('accounting._menu', [ 'active' => 'partner_payments' ])
@endsection

@section('wildcard')
    <div class="card wild-background">
        <div class="container">
            <span class="wildcard-title">Partner Ödemeleri</span>
        </div>
    </div>
@endsection

@section('action-bar')
    <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-tooltip="İşlem Yap" data-trigger="action">
        <i class="material-icons grey-text text-darken-2">account_balance_wallet</i>
    </a>
@endsection

@push('local.styles')
    th {
        font-weight: 400;
        padding: 1rem;
    }
@endpush

@push('local.scripts')

@endpush

@section('content')
    @if ($payments->count())
        <div class="card">
            <table class="highlight">
                <tbody>
                    @foreach($payments as $transaction)
                        <tr>
                            <th>
                                <a href="{{ route('admin.user', $transaction->user->id) }}">{{ $transaction->user->email }}</a>
                                <p class="mb-0">{{ $transaction->message ? $transaction->message : '-' }}</p>
                                <i>
                                    <small class="grey-text">{{ date('d.m.Y H:i', strtotime($transaction->created_at)) }}</small>
                                </i>
                            </th>

                            <th style="white-space: nowrap; vertical-align: top;" class="right-align {{ $transaction->process['color'] }}-text">
                                <p class="mb-0">
                                    @if ($transaction->amount > 0){{ '+' }}@endif{{ $transaction->amount }}
                                    {{ $transaction->currency }}
                                </p>
                                <i>
                                    <small>{{ $transaction->process['title'] }}</small>
                                </i>
                            </th>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {!! $payments->links('vendor.pagination.materializecss') !!}
    @else
        <div class="card card-unstyled">
            <div class="card-content">
                <div class="p-2">
                    @component('components.nothing')@endcomponent
                </div>
            </div>
        </div>
    @endif
@endsection
