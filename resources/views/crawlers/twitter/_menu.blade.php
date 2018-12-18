<div class="collection">
    <a href="{{ route('admin.twitter.settings') }}" class="collection-item waves-effect waves-light {{ $active == 'dashboard' ? 'active' : '' }}">Ana Sayfa</a>
    <a href="{{ route('admin.twitter.indices') }}" class="collection-item waves-effect waves-light {{ $active == 'indices' ? 'active' : '' }}">Index Yönetimi</a>
    <a href="{{ route('admin.twitter.accounts') }}" class="collection-item waves-effect waves-light {{ $active == 'accounts' ? 'active' : '' }}">Bağlı Hesaplar</a>
    <div class="divider grey"></div>
    <a href="{{ route('admin.twitter.stream.accounts') }}" class="collection-item waves-effect waves-light {{ $active == 'stream.accounts' ? 'active' : '' }}">Takip Edilen Kullanıcılar</a>
    <a href="{{ route('admin.twitter.stream.keywords') }}" class="collection-item waves-effect waves-light {{ $active == 'stream.keywords' ? 'active' : '' }}">Takip Edilen Kelimeler</a>
</div>
