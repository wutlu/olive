<div class="collection">
    <a href="{{ route('settings.organisation') }}" class="collection-item waves-effect waves-light {{ $active == 'organisation' ? 'active' : '' }}">Organizasyon</a>
    <a href="{{ route('settings.account') }}" class="collection-item waves-effect waves-light {{ $active == 'account' ? 'active' : '' }}">Hesap Bilgileri</a>
    <a href="{{ route('settings.avatar') }}" class="collection-item waves-effect waves-light {{ $active == 'avatar' ? 'active' : '' }}">Hesap Resmi</a>
    <a href="{{ route('settings.notifications') }}" class="collection-item waves-effect waves-light {{ $active == 'notifications' ? 'active' : '' }}">Bildirim Tercihleri</a>
    <a href="{{ route('settings.api') }}" class="collection-item waves-effect waves-light {{ $active == 'api' ? 'active' : '' }}">Api</a>
    <a href="{{ route('settings.support') }}" class="collection-item waves-effect waves-light {{ $active == 'support' ? 'active' : '' }}">Destek</a>
</div>
