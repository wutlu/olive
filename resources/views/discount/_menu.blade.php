<div class="collection">
    <a href="{{ route('admin.discount.coupon.list') }}" class="collection-item waves-effect waves-light {{ $active == 'coupons' ? 'active' : '' }}">İndirim Kuponları</a>
    <a href="{{ route('admin.discount.day.list') }}" class="collection-item waves-effect waves-light {{ $active == 'days' ? 'active' : '' }}">İndirim Günleri</a>
</div>
