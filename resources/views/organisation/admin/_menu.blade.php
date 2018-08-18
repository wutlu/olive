<div class="collection">
    <a href="{{ route('admin.organisation', $id) }}" class="collection-item waves-effect {{ $active == 'organisation' ? 'active' : '' }}">
        Organizasyon Bilgileri
    </a>
    <a href="{{ route('admin.organisation.invoices', $id) }}" class="collection-item waves-effect {{ $active == 'invoices' ? 'active' : '' }}">
        Fatura Geçmişi <span class="badge teal white-text">{{ $organisation->invoices()->count() }}</span>
    </a>
    <div class="divider teal"></div>
    @forelse($organisation->users as $user)
        <a href="{{ route('admin.user', $user->id) }}" class="collection-item waves-effect">
            {{ $user->name }}
            <p class="grey-text">{{ $user->id == $organisation->user_id ? 'Organizasyon Sahibi' : 'Kullanıcı' }}</p>
        </a>
    @empty
        <a href="{{ route('admin.user', $organisation->author->id) }}" class="collection-item waves-effect">
            {{ $organisation->author->name }}
            <p class="grey-text">Eski Organizasyon Sahibi</p>
        </a>
    @endforelse
</div>
