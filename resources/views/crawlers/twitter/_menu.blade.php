<div class="collection">
    <a href="{{ route('admin.twitter.settings') }}" class="collection-item waves-effect {{ $active == 'dashboard' ? 'active' : '' }}">Ana Sayfa</a>
    <a href="{{ route('admin.twitter.indices') }}" class="collection-item waves-effect {{ $active == 'indices' ? 'active' : '' }}">Index Yönetimi</a>
    <a href="{{ route('admin.twitter.accounts') }}" class="collection-item waves-effect {{ $active == 'accounts' ? 'active' : '' }}">Bağlı Hesaplar</a>
    <a href="#" class="collection-item waves-effect {{ $active == 'tokens' ? 'active' : '' }}" disabled>Kelime Takibi</a>
    <a href="#" class="collection-item waves-effect {{ $active == 'tokens' ? 'active' : '' }}" disabled>Kullanıcı Takibi</a>
</div>
