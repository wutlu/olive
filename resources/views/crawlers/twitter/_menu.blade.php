<div class="collection">
    <a href="{{ route('admin.twitter.settings') }}" class="collection-item waves-effect {{ $active == 'dashboard' ? 'active' : '' }}">Ana Sayfa</a>
    <a href="{{ route('admin.twitter.indices') }}" class="collection-item waves-effect {{ $active == 'indices' ? 'active' : '' }}">Index Yönetimi</a>
    <a href="#" class="collection-item waves-effect {{ $active == 'tokens' ? 'active' : '' }}">Bağlı Hesaplar</a>
    <a href="#" class="collection-item waves-effect {{ $active == 'tokens' ? 'active' : '' }}">Kelime Takibi</a>
    <a href="#" class="collection-item waves-effect {{ $active == 'tokens' ? 'active' : '' }}">Kullanıcı Takibi</a>
</div>
