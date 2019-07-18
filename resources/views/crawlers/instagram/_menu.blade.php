<div class="collection">
    <a href="{{ route('admin.instagram.settings') }}" class="collection-item waves-effect {{ $active == 'dashboard' ? 'active' : '' }}">Instagram Ayarları</a>
    <a href="{{ route('admin.instagram.indices') }}" class="collection-item waves-effect {{ $active == 'indices' ? 'active' : '' }}">Index Yönetimi</a>

    <div class="divider grey"></div>

    <a href="{{ @$organisation ? route('admin.instagram.urls', $organisation->id) : route('admin.instagram.urls') }}" class="collection-item waves-effect {{ $active == 'following.urls' ? 'active' : '' }}">Takip Edilen Bağlantılar</a>
</div>
