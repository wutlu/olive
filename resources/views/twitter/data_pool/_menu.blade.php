<div class="collection">
    <a href="{{ route('twitter.keyword.list') }}" class="collection-item waves-effect {{ $active == 'keywords' ? 'active' : '' }}">Kelime Havuzu</a>
    <a href="{{ route('twitter.account.list') }}" class="collection-item waves-effect {{ $active == 'accounts' ? 'active' : '' }}">Kullanıcı Havuzu</a>
    <a href="{{ route('twitter.connect') }}" class="collection-item waves-effect d-flex">Twitter Bağlantısı</a>
</div>
