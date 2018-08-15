<div class="card-panel card-loader" @isset($id)id="{{ $id }}"@endisset>
    <div class="progress {{ $color }}">
        <div class="indeterminate {{ $color }} lighten-4"></div>
    </div>
    <small class="grey-text">Yükleniyor...</small>
</div>
