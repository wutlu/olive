<div class="card-action d-flex justify-content-between">
    <span class="left-align p-1">
        <small class="d-block grey-text">OLUŞTURULDU</small>
        <time>{{ date('d.m.Y H:i', strtotime($document['_source']['created_at'])) }}</time>
    </span>

    <span class="d-flex align-self-center">
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
            <i class="material-icons grey-text text-darken-2">archive</i>
        </a>
        <span class="p-1"></span>
        <a
            href="#"
            class="btn-floating btn-small waves-effect white json align-self-center"
            data-href="{{ route('content.data', [
                'es_id' => $document['_id'],
                'es_type' => $document['_type'],
                'es_index' => $document['_index']
            ]) }}"
            data-method="post"
            data-callback="__report__data"
            data-tooltip="Rapora Ekle"
            data-id="{{ $document['_id'] }}"
            data-type="{{ $document['_type'] }}"
            data-index="{{ $document['_index'] }}">
            <i class="material-icons grey-text text-darken-2">note_add</i>
        </a>
    </span>

     <span class="right-align p-1">
        <small class="d-block grey-text">SON SENKRONİZAZYON</small>
        <time>{{ date('d.m.Y H:i', strtotime($document['_source']['called_at'])) }}</time>
    </span>
</div>
