<div class="card-action d-flex justify-content-between">
    <span class="left-align">
        <small class="d-block grey-text">OLUŞTURULDU</small>
        <time>{{ date('d.m.Y H:i', strtotime($document['_source']['created_at'])) }}</time>
    </span>

    <a
        href="#"
        class="btn-floating btn-small waves-effect white json align-self-center"
        data-href="{{ route('pin', 'add') }}"
        data-method="post"
        data-include="group_id"
        data-callback="__pin"
        data-error-callback="__pin_dock"
        data-trigger="pin"
        data-id="{{ $document['_id'] }}"
        data-type="{{ $document['_type'] }}"
        data-index="{{ $document['_index'] }}">
        <i class="material-icons grey-text text-darken-2">add</i>
    </a>

     <span class="right-align">
        <small class="d-block grey-text">ALINDI</small>
        <time>{{ date('d.m.Y H:i', strtotime($document['_source']['called_at'])) }}</time>
    </span>
</div>
