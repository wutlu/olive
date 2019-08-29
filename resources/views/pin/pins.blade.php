@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Pin Grupları',
            'link' => route('pin.groups')
        ],
        [
            'text' => $pg->name
        ]
    ],
    'footer_hide' => true
])

@push('local.scripts')
    function __pdf(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({
                html: 'Rapor isteğiniz alındı. Biz raporunuzu hazırlarken, araştırmanıza devam edebilirsiniz.',
                classes: 'green darken-2'
            })
        }
    }
@endpush

@section('action-bar')
    <a
        href="#"
        class="btn-floating btn-large halfway-fab waves-effect white json btn-image"
        data-tooltip="Pdf Dökümü Al"
        data-position="left"
        data-href="{{ route('pin.pdf') }}"
        data-id="{{ $pg->id }}"
        data-method="post"
        data-callback="__pdf"
        style="background-image: url('{{ asset('img/icons/pdf.png') }}');"></a>
@endsection

@section('wildcard')
    <div class="card wild-background">
        <div class="container">
            <span class="wildcard-title">Pinlemeler</span>
        </div>
    </div>
@endsection

@section('content')
    <div class="card card-unstyled mb-1">
        <div class="card-contenet">
            <div class="d-flex justify-content-end">
                @if ($pg->html_to_pdf == 'success')
                    <span class="align-self-center grey-text">{{ date('d.m.Y H:i', strtotime($pg->completed_at)) }}</span>
                    <a href="{{ url($pg->pdf_path).'?v='.date('dmyHi', strtotime($pg->completed_at)) }}" class="btn-flat waves-effect align-self-center ml-1">Pdf İndir</a>
                @endif

                <a href="{{ route('pin.urls', $pg->id) }}" class="btn-flat waves-effect align-self-center ml-1">URL Listesi (csv)</a>
            </div>
        </div>
    </div>

    @forelse ($pins as $pin)
            @php
            $id = $pin->index.'_'.$pin->type.'_'.$pin->id;
            @endphp

            <ul id="dropdown-{{ $id }}" class="dropdown-content">
                <li>
                    <a href="{{ route('content', [ 'es_index' => $pin->index, 'es_type' => $pin->type, 'es_id' => $pin->id ]) }}" class="waves-effect">İncele</a>
                </li>

                @if ($pin->organisation_id == auth()->user()->organisation_id)
                    <li class="divider" tabindex="-1"></li>
                    <li>
                        <a
                            href="#"
                            class="waves-effect json"
                            data-href="{{ route('pin', 'remove') }}"
                            data-method="post"
                            data-id="{{ $pin->id }}"
                            data-type="{{ $pin->type }}"
                            data-index="{{ $pin->index }}"
                            data-group_id="{{ $pg->id }}"
                            data-callback="__pin">Pin'i Kaldır</a>
                    </li>
                @endif
            </ul>

            <div class="card card-data {{ $pin->type }} mb-1" data-id="card-{{ $id }}">
                <div class="card-content">
                    <span class="card-title">
                        {{ $pin->type }}
                        <a href="#" class="dropdown-trigger right" data-target="dropdown-{{ $id }}" data-align="right">
                            <i class="material-icons">more_vert</i>
                        </a>
                    </span>

                    <div class="data-area" id="{{ $pin->id }}"></div>

                    @push('local.scripts')
                    $('#{{ $pin->id }}').html(_{{ $pin->type }}_({!! json_encode(array_merge($pin->content, [ '_id' => $pin->id, '_type' => $pin->type, '_index' => $pin->index ])) !!}))
                    @endpush
                </div>

                @if ($pin->organisation_id == auth()->user()->organisation_id)
                    <div class="card-comment">
                        <div class="input-field">
                            <textarea
                                id="textarea-{{ $id }}"
                                name="comment"
                                class="materialize-textarea json"
                                data-href="{{ route('pin.comment') }}"
                                data-method="post"
                                data-index="{{ $pin->index }}"
                                data-type="{{ $pin->type }}"
                                data-id="{{ $pin->id }}">{{ $pin->comment }}</textarea>
                            <label for="textarea-{{ $id }}">Yorum Girin</label>
                        </div>
                    </div>
                @endif
            </div>
    @empty
        @component('components.nothing')
            @slot('cloud_class', 'white-text')
            @slot('text_class', 'grey-text text-darken-2')
            @slot('size', 'small')
            @slot('text', 'Pinleme Yapılmadı!')
        @endcomponent
    @endforelse

    {!! $pins->links('vendor.pagination.materializecss') !!}
@endsection

@push('local.scripts')
    function __pin(__, obj)
    {
        if (obj.status == 'removed')
        {
            __.closest('.card.card-data').slideUp();
        }
        else if (obj.status == 'failed')
        {
            M.toast({ html: 'Hay aksi, beklenmedik bir durum.', classes: 'orange darken-2' })
        }
    }
@endpush
