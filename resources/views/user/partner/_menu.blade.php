<div class="collection">
    <a href="{{ route('partner.user.list') }}" class="collection-item waves-effect {{ $active == 'list' ? 'active' : '' }}">Kullanıcılar</a>
    <a href="{{ route('partner.history') }}" class="collection-item waves-effect {{ $active == 'account_history' ? 'active' : '' }}">Hesap Geçmişi</a>
</div>
