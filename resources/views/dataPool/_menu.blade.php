<div class="collection">
    <a href="{{ route('data_pool.dashboard') }}" class="collection-item waves-effect {{ $active == 'dashboard' ? 'active' : '' }}">Veri Havuzu</a>
    <div class="divider"></div>
    <a href="{{ route('data_pool.dashboard') }}" class="collection-item waves-effect {{ $active == 'youtube' ? 'active' : '' }}">YouTube Video Havuzu</a>
    <a href="{{ route('data_pool.dashboard') }}" class="collection-item waves-effect {{ $active == 'youtube' ? 'active' : '' }}">YouTube Kanal Havuzu</a>
    <a href="{{ route('data_pool.dashboard') }}" class="collection-item waves-effect {{ $active == 'youtube' ? 'active' : '' }}">YouTube Kelime Havuzu</a>
    <div class="divider"></div>
    <a href="{{ route('twitter.keyword.list') }}" class="collection-item waves-effect {{ $active == 'twitter.keywords' ? 'active' : '' }}">Twitter Kelime Havuzu</a>
    <a href="{{ route('twitter.account.list') }}" class="collection-item waves-effect {{ $active == 'twitter.accounts' ? 'active' : '' }}">Twitter Kullanıcı Havuzu</a>
    <a href="{{ route('twitter.connect') }}" class="collection-item waves-effect d-flex">Twitter Bağlantısı</a>
</div>
