<div class="card-panel card-loader {{ @$class }}" {{ @$id ? 'id='.$id.'' : '' }}>
    <div class="progress {{ $color }}">
        <div class="indeterminate @isset($color){{ implode(' ', [ $color, 'lighten-4' ]) }}@endisset"></div>
    </div>
    <small class="grey-text">Yükleniyor...</small>
</div>
