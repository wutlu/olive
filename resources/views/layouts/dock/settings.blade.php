<div class="collection">
    <a href="{{ route('settings.organisation') }}" class="collection-item waves-effect {{ $active == 'organisation' ? 'active' : '' }}">Organizasyon</a>
    <a href="{{ route('settings.email') }}" class="collection-item waves-effect {{ $active == 'email' ? 'active' : '' }}">E-posta</a>
    <a href="{{ route('settings.password') }}" class="collection-item waves-effect {{ $active == 'password' ? 'active' : '' }}">Şifre</a>
    <a href="{{ route('settings.account') }}" class="collection-item waves-effect {{ $active == 'account' ? 'active' : '' }}">Hesap Bilgileri</a>
    <a href="{{ route('settings.notification') }}" class="collection-item waves-effect {{ $active == 'notification' ? 'active' : '' }}">Bildirim Tercihleri</a>
    <a href="{{ route('settings.api') }}" class="collection-item waves-effect {{ $active == 'api' ? 'active' : '' }}">Api</a>
    <a href="{{ route('settings.support') }}" class="collection-item waves-effect {{ $active == 'contact' ? 'active' : '' }}">Destek</a>
</div>
