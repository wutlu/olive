<div class="collection collection-unstyled">
    <a href="{{ route('trend.live') }}" class="collection-item waves-effect d-flex waves-effect {{ $active == 'live' ? 'active' : '' }}">
        <i class="material-icons align-self-center">trending_up</i>
        <span class="align-self-center">Canlı Trend</span>
    </a>
    <a href="{{ route('trend.archive') }}" class="collection-item waves-effect d-flex waves-effect {{ $active == 'archive' ? 'active' : '' }}">
        <i class="material-icons align-self-center">archive</i>
        <span class="align-self-center">Trend Arşivi</span>
    </a>
    <a href="{{ route('trend.popular') }}" class="collection-item waves-effect d-flex waves-effect {{ $active == 'popular' ? 'active' : '' }}">
        <i class="material-icons align-self-center">people</i>
        <span class="align-self-center">Popüler Kaynaklar</span>
    </a>
</div>
