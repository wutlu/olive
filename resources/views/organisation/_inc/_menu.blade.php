<div class="collection">
    <a href="{{ route('admin.organisation.list') }}" class="collection-item waves-effect {{ $active == 'list' ? 'active' : '' }}">Organizasyonlar</a>
    <a href="{{ route('admin.organisation.price.settings') }}" class="collection-item waves-effect {{ $active == 'price_settings' ? 'active' : '' }}">Fiyatlandırma Ayarları</a>
</div>
