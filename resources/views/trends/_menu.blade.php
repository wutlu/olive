<div class="collection">
    <a href="{{ route('trend.live') }}" class="collection-item waves-effect waves-light {{ $active == 'trends' ? 'active' : '' }}">Canlı Trend</a>
    <a href="{{ route('trend.archive') }}" class="collection-item waves-effect waves-light {{ $active == 'archive' ? 'active' : '' }}">Trend Arşivi</a>
    <a href="{{ route('trend.index') }}" class="collection-item waves-effect waves-light {{ $active == 'trend_index' ? 'active' : '' }}">Trend Endeksi</a>
</div>
