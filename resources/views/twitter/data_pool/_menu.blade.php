<div class="collection">
    <a href="{{ route('twitter.keyword.list') }}" class="collection-item waves-effect waves-light {{ $active == 'keywords' ? 'active' : '' }}">Kelime Havuzu</a>
    <a href="{{ route('twitter.account.list') }}" class="collection-item waves-effect waves-light {{ $active == 'accounts' ? 'active' : '' }}">Kullanıcı Havuzu</a>
    <a href="{{ route('twitter.connect') }}" class="collection-item waves-effect waves-light d-flex">Twitter Bağlantısı</a>
</div>
