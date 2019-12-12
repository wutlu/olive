@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Raporlar'
        ]
    ],
    'footer_hide' => true,
    'report_menu' => true
])

@section('wildcard')
    <form method="get" action="{{ route('report.dashboard') }}">
        <div class="card">
            <div class="container">
                <div class="wildcard-searchground">
                    <span class="wildcard-title">
                        Raporlar
                        <small class="d-table" data-name="total">{{ $data->total() }}</small>
                    </span>
                    <div class="wildcard-search">
                        <input type="text" name="q" id="q" placeholder="Arayın" value="{{ $q }}" />
                        @if ($q)
                            <a href="{{ route('report.dashboard') }}" class="clear">
                                <i class="material-icons">close</i>
                            </a>
                        @endif
                    </div>
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
                    <li class="collection-item {{ $item->status() ? '' : 'red' }} lighten-5" data-item-id="item-{{ $item->id }}">
                        <span>
                            <span>{{ $item->name }}</span>
                            <a class="blue-grey-text align-self-center" href="{{ route('user.profile', $item->user_id) }}">{{ '@'.$item->user->name }}</a>
                        </span>
                        <div class="d-flex mt-1">
                            <span class="align-self-center" style="width: 128px;">{{ date('d.m.Y H:i', strtotime($item->created_at)) }}</span>
                            <div class="align-self-center hide-on-med-and-down">
                                <div class="d-flex">
                                    <span class="align-self-center d-flex pl-1 pr-1">
                                        <i class="material-icons align-self-center mr-1">insert_drive_file</i>
                                        <span class="align-self-center" style="width: 32px;">{{ $item->pages->count() }}</span>
                                    </span>
                                    <span class="align-self-center d-flex pl-1 pr-1">
                                        <i class="material-icons align-self-center mr-1">remove_red_eye</i>
                                        <span class="align-self-center" style="width: 48px;">{{ $item->hit }}</span>
                                    </span>
                                    <span class="align-self-center d-flex pl-1 pr-1">
                                        <i class="material-icons align-self-center mr-1">lock</i>
                                        <span class="align-self-center" style="width: 48px;">{{ $item->hit_with_password }}</span>
                                    </span>
                                </div>
                            </div>
                            <span class="align-self-center d-flex ml-auto">
                                @if ($item->password)
                                    <a href="#" class="orange-text mr-1" data-modal-alert="{{ $item->password }}" data-modal-alert-title="Şifre">
                                        <i class="material-icons">star</i>
                                    </a>
                                @endif
                                <a target="_blank" href="{{ route('report.view', $item->key) }}" class="grey-text text-darken-2 mr-1">
                                    <i class="material-icons">pageview</i>
                                </a>
                                <a href="{{ route('report.edit', $item->id) }}" class="grey-text text-darken-2 mr-1">
                                    <i class="material-icons">edit</i>
                                </a>
                                <a href="#" class="grey-text text-darken-2" data-trigger="delete" data-id="{{ $item->id }}">
                                    <i class="material-icons">delete</i>
                                </a>
                            </span>
                        </div>
                        @if ($item->status)
                            <div class="hide-on-med-and-down">
                                <div class="d-flex justify-content-between teal lighten-5 mt-1">
                                    <span class="align-self-center p-1"> 
                                        @if ($item->status == 'creating')
                                            Oluşturuluyor
                                        @elseif ($item->status == 'creating')
                                            Gönderiliyor
                                        @else
                                            Tamamlandı
                                        @endif
                                    </span>
                                    <span class="align-self-center p-1">{{ $item->subject }}</span>
                                    <span class="align-self-center p-1">{{ $item->gsm }}</span>
                                </div>
                            </div>
                        @endif
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
                    'html': keywords.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<a />', {
                    'href': '#',
                    'class': 'waves-effect btn-flat red-text json',
                    'html': keywords.ok,
                    'data-href': '{{ route('report.delete') }}',
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
                window.location = '{{ route('report.dashboard') }}';
            }
            else
            {
                var total = $('[data-name=total]')
                    total.html(total.html() - 1)
            }
        }
    }
@endpush
