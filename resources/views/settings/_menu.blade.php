<div class="collection white z-depth-1">
    <a href="{{ route('settings.organisation') }}" class="collection-item waves-effect {{ $active == 'organisation' ? 'active' : '' }}">Organizasyon</a>
    <a href="{{ route('settings.account') }}" class="collection-item waves-effect {{ $active == 'account' ? 'active' : '' }}">Hesap Bilgileri</a>
    <a href="{{ route('settings.avatar') }}" class="collection-item waves-effect {{ $active == 'avatar' ? 'active' : '' }}">Hesap Resmi</a>
    <a href="{{ route('settings.notifications') }}" class="collection-item waves-effect {{ $active == 'notifications' ? 'active' : '' }}">Bildirim Tercihleri</a>
    <a href="{{ route('settings.support') }}" class="collection-item waves-effect {{ $active == 'support' ? 'active' : '' }}">Destek</a>
</div>
