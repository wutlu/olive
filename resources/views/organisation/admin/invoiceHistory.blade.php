@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Organizasyonlar',
            'link' => route('admin.organisation.list')
        ],
        [
            'text' => $organisation->name,
            'link' => route('admin.organisation', $organisation->id)
        ],
        [
            'text' => '🐞 Fatura Geçmişi'
        ]
    ],
    'dock' => true,
    'footer_hide' => true
])

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Fatura Geçmişi</span>
        </div>
        @if ($organisation->savedSearches->count())
            <div class="collection collection-unstyled">
                @foreach($organisation->invoices()->paginate(5) as $invoice)
                <a href="{{ route('organisation.invoice', $invoice->invoice_id) }}" class="collection-item d-flex justify-content-between waves-effect {{ $invoice->paid_at ? 'green-text' : 'red-text' }}">
                    <i class="material-icons align-self-center">history</i>
                    <span class="align-self-center">
                        <p class="mb-0">#{{ $invoice->invoice_id }}</p>
                        <p class="mb-0 grey-text">{{ date('d.m.Y H:i', strtotime($invoice->created_at)) }}</p>
                    </span>
                    <span class="ml-auto {{ $invoice->paid_at ? 'green-text' : 'red-text' }}">{{ $invoice->paid_at ? date('d.m.Y H:i', strtotime($invoice->paid_at)) : 'ÖDENMEDİ' }}</span>
                </a>
                @endforeach
            </div>
        @else
            <div class="card-content">
                @component('components.nothing')@endcomponent
            </div>
        @endif
    </div>

	{!! $organisation->invoices()->paginate(5)->links('vendor.pagination.materializecss') !!}
@endsection

@section('dock')
    @include('organisation.admin._menu', [ 'active' => 'invoices', 'id' => $organisation->id ])
@endsection
