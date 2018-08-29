@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Kullanıcılar',
            'link' => route('admin.user.list')
        ],
        [
            'text' => $user->name,
            'link' => route('admin.user', $user->id)
        ],
        [
            'text' => 'Fatura Geçmişi'
        ]
    ],
    'dock' => true
])

@section('content')
    <div class="card">
        <div class="card-image">
            <img src="{{ asset('img/md-s/10.jpg') }}" alt="Fatura Geçmişi" />
            <span class="card-title">Fatura Geçmişi</span>
        </div>
        @if ($user->invoices->count())
            <div class="collection">
                @foreach($user->invoices()->paginate(5) as $invoice)
                <a href="{{ route('organisation.invoice', $invoice->invoice_id) }}" class="collection-item d-flex waves-effect {{ $invoice->paid_at ? 'grey-text' : 'red-text' }}">
                    <i class="material-icons align-self-center">history</i>
                    <span class="align-self-center">
                        <p>{{ $invoice->plan()->name }} ({{ $invoice->plan()->properties->capacity->value }} kullanıcı)</p>
                        <p class="grey-text">{{ date('d.m.Y H:i', strtotime($invoice->created_at)) }}</p>
                    </span>
                    <small class="badge ml-auto">{{ $invoice->paid_at ? date('d.m.Y H:i', strtotime($invoice->paid_at)) : 'ÖDENMEDİ' }}</small>
                </a>
                @endforeach
            </div>
        @else
            <div class="card-content">
                <div class="not-found">
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">wb_sunny</i>
                    <p>Fatura Geçmişi Yok</p>
                </div>
            </div>
        @endif
    </div>
	{!! $user->invoices()->paginate(5)->links('vendor.pagination.materializecss') !!}
@endsection

@section('dock')
    @include('user.admin._menu', [ 'active' => 'invoices', 'id' => $user->id ])
@endsection

@push('local.scripts')
    $('select').formSelect()
    $('.tabs').tabs()
@endpush
