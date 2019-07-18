<div class="collection">
    <a href="{{ route('admin.organisation', $id) }}" class="collection-item waves-effect {{ $active == 'organisation' ? 'active' : '' }}">
        Organizasyon Bilgileri
    </a>
    <a href="{{ route('admin.organisation.invoices', $id) }}" class="collection-item waves-effect {{ $active == 'invoices' ? 'active' : '' }}">
        Fatura Geçmişi <span class="badge teal white-text">{{ $organisation->invoices()->count() }}</span>
    </a>
    <div class="divider"></div>

    <div class="collection-item pb-0">
        <span class="grey-text">Kullanıcılar</span>
    </div> 
    @forelse($organisation->users as $user)
        <a href="{{ route('admin.user', $user->id) }}" class="collection-item waves-effect">
            {{ $user->name }}
            <p class="grey-text mb-0">{{ $user->id == $organisation->user_id ? 'Organizasyon Sahibi' : 'Kullanıcı' }}</p>
        </a>
    @empty
        <a href="{{ route('admin.user', $organisation->author->id) }}" class="collection-item waves-effect">
            {{ $organisation->author->name }}
            <p class="grey-text mb-0">Eski Organizasyon Sahibi</p>
        </a>
    @endforelse

    <div class="divider"></div>

    <a href="{{ route('admin.organisation.keyword_groups', $organisation->id) }}" class="collection-item waves-effect {{ $active == 'groups.keyword' ? 'active' : '' }}">Gerçek Zamanlı Kelime Grupları</a>
    <a href="{{ route('admin.organisation.pin_groups', $organisation->id) }}" class="collection-item waves-effect {{ $active == 'groups.pin' ? 'active' : '' }}">Pin Grupları</a>
    <a href="{{ route('admin.organisation.alarms', $organisation->id) }}" class="collection-item waves-effect {{ $active == 'alarms' ? 'active' : '' }}">Alarmlar</a>

    <div class="divider"></div>

    <div class="collection-item">
        <span class="grey-text">YouTube</span>
    </div>
    <a href="{{ route('admin.youtube.followed_channels', $organisation->id) }}" class="collection-item waves-effect">Takip Ettiği Kanallar</a>
    <a href="{{ route('admin.youtube.followed_videos', $organisation->id) }}" class="collection-item waves-effect">Takip Ettiği Videoları</a>
    <a href="{{ route('admin.youtube.followed_keywords', $organisation->id) }}" class="collection-item waves-effect">Takip Ettiği Kelimeler</a>

    <div class="divider"></div>

    <div class="collection-item">
        <span class="grey-text">Twitter</span>
    </div>
    <a href="{{ route('admin.twitter.stream.keywords', $organisation->id) }}" class="collection-item waves-effect">Takip Ettiği Kelimeler</a>
    <a href="{{ route('admin.twitter.stream.accounts', $organisation->id) }}" class="collection-item waves-effect">Takip Ettiği Kullanıcılar</a>

    <div class="collection-item">
        <span class="grey-text">Instagram</span>
    </div>
    <a href="{{ route('admin.instagram.urls', $organisation->id) }}" class="collection-item waves-effect">Takip Ettiği Bağlantılar</a>
</div>
