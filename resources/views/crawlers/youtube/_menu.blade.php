<div class="collection">
    <a href="{{ route('admin.youtube.settings') }}" class="collection-item waves-effect {{ $active == 'youtube.settings' ? 'active' : '' }}">YouTube Ayarları</a>
    <a href="{{ route('admin.youtube.indices') }}" class="collection-item waves-effect {{ $active == 'indices' ? 'active' : '' }}">Index Yönetimi</a>

    <div class="divider grey"></div>

    <a href="{{ @$organisation ? route('admin.youtube.followed_channels', $organisation->id) : route('admin.youtube.followed_channels') }}" class="collection-item waves-effect {{ $active == 'youtube.channels' ? 'active' : '' }}">Takip Edilen Kanallar</a>
    <a href="{{ @$organisation ? route('admin.youtube.followed_videos', $organisation->id) : route('admin.youtube.followed_videos') }}" class="collection-item waves-effect {{ $active == 'youtube.videos' ? 'active' : '' }}">Takip Edilen Videolar</a>
    <a href="{{ @$organisation ? route('admin.youtube.followed_keywords', $organisation->id) : route('admin.youtube.followed_keywords') }}" class="collection-item waves-effect {{ $active == 'youtube.keywords' ? 'active' : '' }}">Takip Edilen Kelimeler</a>
</div>
