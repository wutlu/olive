@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'İçerik Sınıflandırma'
        ]
    ],
    'footer_hide' => true,
    'report_menu' => true
])

@section('wildcard')
    <form method="get" action="">
        <div class="card">
            <div class="container">
                <div class="d-flex flex-wrap justify-content-between">
                    <span class="wildcard-title align-self-center">
                        İçerik Sınıflandırma
                        <small class="d-table" data-name="total">{{ $data->total() }}</small>
                    </span>
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
                    <li class="collection-item d-flex justify-content-end" data-item-id="item-{{ $item->id }}">
                        <span class="d-flex align-self-center mr-auto">
                            <a class="align-self-center" href="{{ route('content', [ 'es_index' => $item->_index, 'es_type' => $item->_type, 'es_id' => $item->_id ]) }}" target="_blank">{{ $item->_index }}</a>
                        </span>
                        @if ($item->sentiment)
                            <span class="align-self-center mr-1">{{ config('system.analysis.sentiment.types.sentiment-'.$item->sentiment)['title'] }}</span>
                        @endif
                        @if ($item->consumer)
                            <span class="align-self-center mr-1">{{ config('system.analysis.consumer.types.consumer-'.$item->consumer)['title'] }}</span>
                        @endif
                        @if ($item->category)
                            <span class="align-self-center mr-1">{{ config('system.analysis.category.types')[$item->category]['title'] }}</span>
                        @endif
                        <span class="align-self-center mr-1">{{ $item->user->email }}</span>
                        <span class="align-self-center mr-1">{{ date('d.m.Y H:i', strtotime($item->created_at)) }}</span>
                        <a href="#" class="btn-flat btn-floating waves-effect align-self-center" data-trigger="delete" data-id="{{ $item->id }}">
                            <i class="material-icons">check</i>
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
            <span class="d-table mx-auto">{!! $data->appends()->links('vendor.pagination.materializecss') !!}</span>
        @endif
    </div>
@endsection

@push('local.scripts')
    $(document).on('click', '[data-trigger=delete]', function() {
        return modal({
            'id': 'alert',
            'body': 'Sorunu düzelttiniz mi?',
            'size': 'modal-small',
            'title': 'Durum',
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
                    'class': 'waves-effect btn-flat json',
                    'html': buttons.yes,
                    'data-href': '{{ route('reported_contents.delete') }}',
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
                window.location = '{{ route('reported_contents') }}';
            }
            else
            {
                var total = $('[data-name=total]')
                    total.html(total.html() - 1)
            }
        }
    }
@endpush
