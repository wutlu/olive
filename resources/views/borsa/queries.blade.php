@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => '🐞 Borsa Sorguları'
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
                        Borsa Sorguları
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
                    <li class="collection-item" data-item-id="item-{{ $item->id }}">
                        <div class="d-flex justify-content-between">
                            <span class="align-self-center">{{ $item->name }}</span>
                            <a
                                href="#"
                                class="btn-flat btn-floating waves-effect align-self-center json"
                                data-href="{{ route('borsa.query', $item->id) }}"
                                data-id="{{ $item->id }}"
                                data-method="post"
                                data-callback="__edit_form">
                                <i class="material-icons">edit</i>
                            </a>
                        </div>
                        <div class="d-flex mt-1">
                            <span data-name="query-pos" style="width: 50%;" class="p-1 flex-fill {{ $item->query_pos ? 'green lighten-5' : 'grey lighten-4' }} green-text">{{ $item->query_pos }}</span>
                            <span data-name="query-neg" style="width: 50%;" class="p-1 flex-fill {{ $item->query_neg ? 'red lighten-5' : 'grey lighten-4' }} red-text">{{ $item->query_neg }}</span>
                        </div>
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
    function __edit_form(__, obj)
    {
        var mdl = modal({
            'id': 'detail',
            'body': $('<form />', {
                'method': 'patch',
                'action': '{{ route('borsa.query.update') }}',
                'id': 'form',
                'data-id': __.data('id'),
                'data-callback': '__update_query',
                'class': 'json',
                'html': [
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<input />', {
                                'id': 'query_pos',
                                'name': 'query_pos',
                                'type': 'text',
                                'class': 'validate',
                                'value': obj.data.query_pos
                            }),
                            $('<label />', {
                                'for': 'query_pos',
                                'html': 'Pozitif Sorgu'
                            }),
                            $('<span />', {
                                'class': 'helper-text',
                                'html': 'Lütfen arama motorunu yalın bir şekilde kullanarak ilgili hisse ile alakalı <span class="green white-text">pozitif</span> bir sorgu oluşturun.'
                            })
                        ]
                    }),
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<input />', {
                                'id': 'query_neg',
                                'name': 'query_neg',
                                'type': 'text',
                                'class': 'validate',
                                'value': obj.data.query_neg
                            }),
                            $('<label />', {
                                'for': 'query_neg',
                                'html': 'Negatif Sorgu'
                            }),
                            $('<span />', {
                                'class': 'helper-text',
                                'html': 'Lütfen arama motorunu yalın bir şekilde kullanarak ilgili hisse ile alakalı <span class="red white-text">negatif</span> bir sorgu oluşturun.'
                            })
                        ]
                    })
                ]
            }),
            'size': 'modal-medium',
            'title': 'Sorgu Güncelle',
            'options': {
                dismissible: false
            },
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect btn-flat grey-text',
                    'html': buttons.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<button />', {
                    'type': 'submit',
                    'class': 'waves-effect btn-flat',
                    'data-submit': 'form#form',
                    'html': buttons.update
                })
            ]
        })

        return mdl;
    }

    function __update_query(__, obj)
    {
        if (obj.status == 'ok')
        {
            var item = $('[data-item-id=item-' + obj.data.id + ']');
                item.find('[data-name=query-pos]')
                    .html(obj.data.query_pos)
                    .addClass(obj.data.query_pos ? 'green lighten-5' : 'grey lighten-4')
                    .removeClass(obj.data.query_pos ? 'grey lighten-4' : 'green lighten-5')
                item.find('[data-name=query-neg]')
                    .html(obj.data.query_neg)
                    .addClass(obj.data.query_neg ? 'red lighten-5' : 'grey lighten-4')
                    .removeClass(obj.data.query_neg ? 'grey lighten-4' : 'red lighten-5')

            $('#modal-detail').modal('close')
        }
    }
@endpush
