<div class="collection mb-1">
    <a href="{{ route('crawlers.blog.list') }}" class="collection-item waves-effect {{ $active == 'list' ? 'active' : '' }}">Blog Botları</a>
    <a href="{{ route('crawlers.blog.indices') }}" class="collection-item waves-effect {{ $active == 'indices' ? 'active' : '' }}">Index Yönetimi</a>
</div>
