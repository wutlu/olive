<div class="collection white z-depth-1 mb-1">
    <a href="{{ route('crawlers.media.list') }}" class="collection-item waves-effect {{ $active == 'list' ? 'active' : '' }}">Medya Botları</a>
    <a href="{{ route('crawlers.media.indices') }}" class="collection-item waves-effect {{ $active == 'indices' ? 'active' : '' }}">Index Yönetimi</a>
</div>
