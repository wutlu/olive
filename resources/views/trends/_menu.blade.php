@if ($price == 0)
    <small class="d-block center-align blue-grey darken-2 white-text mb-1 p-1">
        <i class="material-icons d-table mx-auto mb-1">sentiment_very_satisfied</i>
        BU HİZMET ÜCRETSİZDİR!
    </small>
@endif

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
