<div class="collection">
    <a href="{{ route('admin.user', $id) }}" class="collection-item waves-effect {{ $active == 'account' ? 'active' : '' }}">
        Hesap Bilgileri
    </a>
    <a href="{{ route('admin.user.notifications', $id) }}" class="collection-item waves-effect {{ $active == 'notifications' ? 'active' : '' }}">
        E-posta Bildirimleri
    </a>
    <a href="{{ route('admin.user.invoices', $id) }}" class="collection-item waves-effect {{ $active == 'invoices' ? 'active' : '' }}">
        Fatura Geçmişi <span class="badge teal white-text">{{ $user->invoices()->count() }}</span>
    </a>
    <a href="{{ route('admin.user.tickets', $id) }}" class="collection-item waves-effect {{ $active == 'tickets' ? 'active' : '' }}">
        Destek Talepleri <span class="badge teal white-text">{{ $user->tickets()->count() }}</span>
    </a>
    @if (@$user->organisation)
        <a href="{{ route('admin.organisation', $user->organisation->id) }}" class="collection-item waves-effect">
            {{ $user->organisation->name }}
        </a>
    @endif
</div>
