<div class="collection">
    <a href="{{ route('admin.twitter.settings') }}" class="collection-item waves-effect {{ $active == 'dashboard' ? 'active' : '' }}">Twitter Ayarları</a>
    <a href="{{ route('admin.twitter.indices') }}" class="collection-item waves-effect {{ $active == 'indices' ? 'active' : '' }}">Index Yönetimi</a>
    <a href="{{ route('admin.twitter.accounts') }}" class="collection-item waves-effect {{ $active == 'accounts' ? 'active' : '' }}">Bağlı Hesaplar</a>
    <div class="divider grey"></div>
    <a href="{{ route('admin.twitter.stream.accounts') }}" class="collection-item waves-effect {{ $active == 'stream.accounts' ? 'active' : '' }}">Takip Edilen Kullanıcılar</a>
    <a href="{{ route('admin.twitter.stream.keywords') }}" class="collection-item waves-effect {{ $active == 'stream.keywords' ? 'active' : '' }}">Takip Edilen Kelimeler</a>
</div>
