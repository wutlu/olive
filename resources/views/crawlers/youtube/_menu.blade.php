<div class="collection">
    <a href="{{ route('admin.youtube.settings') }}" class="collection-item waves-effect {{ $active == 'youtube.settings' ? 'active' : '' }}">YouTube Ayarları</a>
    <div class="divider grey"></div>
    <a href="{{ route('admin.youtube.followed_channels') }}" class="collection-item waves-effect {{ $active == 'youtube.channels' ? 'active' : '' }}">Takip Edilen Kanallar</a>
    <a href="{{ route('admin.youtube.followed_videos') }}" class="collection-item waves-effect {{ $active == 'youtube.videos' ? 'active' : '' }}">Takip Edilen Videolar</a>
    <a href="{{ route('admin.youtube.followed_keywords') }}" class="collection-item waves-effect {{ $active == 'youtube.keywords' ? 'active' : '' }}">Takip Edilen Kelimeler</a>
</div>
