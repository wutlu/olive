<div class="collection">
    <a href="{{ route('admin.partner.history') }}" class="collection-item waves-effect {{ $active == 'partner_payments' ? 'active' : '' }}">Partner Ödemeleri</a>
    <a href="{{ route('admin.invoices') }}" class="collection-item waves-effect {{ $active == 'invoices' ? 'active' : '' }}">Faturalar</a>
</div>
