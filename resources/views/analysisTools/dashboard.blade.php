@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Analiz Araçları'
        ]
    ],
    'footer_hide' => true
])

@section('wildcard')
    <form method="get" action="">
        <div class="card">
            <div class="container">
                <div class="d-flex flex-wrap justify-content-between">
                    <span class="wildcard-title align-self-center">
                        Analiz Araçları
                        <small class="d-table">{{ $data->total() }} / {{ $user->organisation->analysis_tools_limit }}</small>
                    </span>
                        <input type="text" name="q" id="q" class="align-self-center sub-search" placeholder="Arayın" value="{{ $q }}" />
                </div>
            </div>
        </div>
    </form>
@endsection

@push('local.scripts')
    $(document).on('click', '[data-trigger=delete]', function() {
        return modal({
            'id': 'alert',
            'body': 'Silmek istediğinizden emin misiniz?',
            'size': 'modal-small',
            'title': 'Sil',
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect grey-text btn-flat',
                    'html': buttons.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<a />', {
                    'href': '#',
                    'class': 'waves-effect btn-flat red-text json',
                    'html': buttons.ok,
                    'data-href': '{{ route('analysis_tools.analysis.delete') }}',
                    'data-method': 'delete',
                    'data-id': $(this).data('id'),
                    'data-callback': '__delete'
                })
            ],
            'options': {}
        })
    })

    function __delete(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('[data-item-id=item-' + obj.data.id + ']').remove()

            $('#modal-alert').modal('close')

            if ($('[data-item-id]').length == 0)
            {
                window.location = '{{ route('analysis_tools.dashboard') }}';
            }
        }
    }
@endpush

@section('content')
    <div class="card card-unstyled">
        @if (count($data))
            <ul class="collection collection-unstyled">
                @foreach ($data as $item)
                    <li class="collection-item d-flex justify-content-between" data-item-id="item-{{ $item->id }}">
                        <span class="align-self-center">
                            <span class="d-flex">
                                <a href="{{ $item->link() }}" class="align-self-center mr-1" target="_blank">
                                    <img align="{{ $item->platform }}" src="{{ asset('img/logos/'.$item->platform.'.svg') }}" style="width: 24px; height: 24px;" />
                                </a>
                                <span class="grey-text">
                                    <a href="#" class="d-table">{{ $item->social_title }}</a>
                                    <span class="d-table">{{ date('d.m.Y', strtotime($item->created_at)) }}</span>
                                </span>
                            </span>
                        </span>
                        <a href="#" class="btn-flat btn-floating waves-effect align-self-center" data-trigger="delete" data-id="{{ $item->id }}">
                            <i class="material-icons">delete</i>
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="card-content">
                @component('components.nothing')@endcomponent
            </div>
        @endif
        @if ($data->total() > $pager)
            <span class="d-table mx-auto">{!! $data->appends([ 'q' => $q ])->links('vendor.pagination.materializecss') !!}</span>
        @endif
    </div>
@endsection
