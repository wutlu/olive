<div class="collection">
    <a href="{{ route('admin.discount.coupon.list') }}" class="collection-item waves-effect {{ $active == 'coupons' ? 'active' : '' }}">İndirim Kuponları</a>
    <a href="{{ route('admin.discount.day.list') }}" class="collection-item waves-effect {{ $active == 'days' ? 'active' : '' }}">İndirim Günleri</a>
</div>
