<div class="collection">
    <a href="{{ route('data_pool.dashboard') }}" class="collection-item waves-effect {{ $active == 'dashboard' ? 'active' : '' }}">Veri Havuzu</a>
    <div class="divider"></div>
    <a href="{{ route('youtube.channel.list') }}" class="collection-item waves-effect {{ $active == 'youtube.channels' ? 'active' : '' }}">YouTube Kanal Havuzu</a>
    <a href="{{ route('youtube.video.list') }}" class="collection-item waves-effect {{ $active == 'youtube.videos' ? 'active' : '' }}">YouTube Video Havuzu</a>
    <a href="{{ route('youtube.keyword.list') }}" class="collection-item waves-effect {{ $active == 'youtube.keywords' ? 'active' : '' }}">YouTube Kelime Havuzu</a>
    <div class="divider"></div>
    <a href="{{ route('twitter.keyword.list') }}" class="collection-item waves-effect {{ $active == 'twitter.keywords' ? 'active' : '' }}">Twitter Kelime Havuzu</a>
    <a href="{{ route('twitter.account.list') }}" class="collection-item waves-effect {{ $active == 'twitter.accounts' ? 'active' : '' }}">Twitter Kullanıcı Havuzu</a>
    <div class="divider"></div>
    <a href="{{ route('instagram.url.list') }}" class="collection-item waves-effect {{ $active == 'instagram.urls' ? 'active' : '' }}">Instagram Bağlantı Havuzu</a>
</div>
