@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Domain Tespiti'
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
                        Domain Tespiti
                        <small class="d-table" data-name="total">{{ $data->total() }}</small>
                    </span>
                    <input type="text" name="q" id="q" class="align-self-center sub-search" placeholder="Arayın" value="{{ $q }}" />
                </div>
            </div>
        </div>
    </form>
@endsection

@section('content')
    <div class="card card-unstyled">
        @if (count($data))
            <ul class="collection collection-unstyled">
                @foreach ($data as $item)
                    <li class="collection-item d-flex justify-content-end {{ $item->color() }}" data-item-id="item-{{ $item->id }}">
                        <span class="d-flex align-self-center mr-auto">
                            <a href="#" class="btn-flat btn-floating waves-effect align-self-center mr-1 json" data-href="{{ route('domain.check') }}" data-method="post" data-id="{{ $item->id }}" data-callback="__check">
                                <i class="material-icons white-text">refresh</i>
                            </a>
                            <a class="align-self-center white-text" href="{{ $item->domain }}" target="_blank">{{ $item->domain }}</a>
                        </span>
                        <span class="align-self-center mr-1 white-text">{{ date('d.m.Y H:i', strtotime($item->created_at)) }}</span>
                        <a href="#" class="btn-flat btn-floating waves-effect align-self-center" data-trigger="delete" data-id="{{ $item->id }}">
                            <i class="material-icons white-text">delete</i>
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

@push('local.scripts')
    function __check(__, obj)
    {
        if (obj.status == 'ok')
        {
            __.closest('.collection-item').removeClass('red blue green').addClass(obj.data.color)

            return modal({
                'id': 'message',
                'title': 'Bilgi',
                'body': obj.data.message,
                'size': 'modal-small',
                'options': {},
                'footer': [
                    $('<a />', {
                        'href': '#',
                        'class': 'modal-close waves-effect btn-flat',
                        'html': buttons.ok
                    })
                ]
            })
        }
    }

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
                    'data-href': '{{ route('domain.delete') }}',
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
                window.location = '{{ route('domain.dashboard') }}';
            }
            else
            {
                var total = $('[data-name=total]')
                    total.html(total.html() - 1)
            }
        }
    }
@endpush
