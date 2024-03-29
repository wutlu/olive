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
    <a href="{{ route('forum.group', [ __('route.forum.user'), $id ]) }}" class="collection-item waves-effect">
        Açtığı Konular <span class="badge teal white-text">{{ $user->messages()->whereNull('message_id')->count() }}</span>
    </a>
    <a href="{{ route('admin.user.search_history', $id) }}" class="collection-item waves-effect {{ $active == 'search_history' ? 'active' : '' }}">
        Arama Geçmişi <span class="badge teal white-text">{{ $user->searchHistory()->count() }}</span>
    </a>
    @if ($user->organisation_id)
        <div class="divider"></div>
        <a href="{{ route('admin.organisation', $user->organisation->id) }}" class="collection-item waves-effect">
            <span class="orange white-text">{{ $user->organisation->name }}</span>
            <span class="d-table grey-text">Organizasyon</span>
        </a>
    @endif
    @if ($user->partner)
        <div class="divider"></div>
        <a href="{{ route('admin.user.list', [ 'q' => 'partner:'.$user->id ]) }}" class="collection-item waves-effect">Referans Geçmişi</a>
        <a href="{{ route('admin.partner.history', [ 'q' => $user->email ]) }}" class="collection-item waves-effect">Ödeme Geçmişi</a>
    @endif
</div>
