<div class="collection">
    <a href="{{ route('settings.organisation') }}" class="collection-item waves-effect {{ $active == 'organisation' ? 'active' : '' }}">Organizasyon</a>
    <a href="{{ route('settings.account') }}" class="collection-item waves-effect {{ $active == 'account' ? 'active' : '' }}">Hesap Bilgileri</a>
    <a href="{{ route('settings.mobile') }}" class="collection-item waves-effect {{ $active == 'mobile' ? 'active' : '' }}">Mobil</a>
    <a href="{{ route('settings.avatar') }}" class="collection-item waves-effect {{ $active == 'avatar' ? 'active' : '' }}">Hesap Resmi</a>
    <a href="{{ route('settings.notifications') }}" class="collection-item waves-effect {{ $active == 'notifications' ? 'active' : '' }}">Bildirim Tercihleri</a>
    <a href="{{ route('settings.search_history') }}" class="collection-item waves-effect {{ $active == 'search_history' ? 'active' : '' }}">Arama Geçmişi</a>
    <a href="{{ route('settings.support') }}" class="collection-item waves-effect {{ $active == 'support' ? 'active' : '' }}">Destek</a>
</div>
