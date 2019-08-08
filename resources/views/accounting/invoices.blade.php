@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Muhasebe'
        ],
        [
            'text' => '🐞 Faturalar'
        ]
    ],
    'footer_hide' => true,
    'dock' => true
])

@section('dock')
    @include('accounting._menu', [ 'active' => 'invoices' ])

    <div class="card card-unstyled mb-1">
        <div class="card-content">
            <span class="card-title d-flex">
                <i class="material-icons mr-1">date_range</i>
                Tarih Aralığı
            </span>
            <div class="collection collection-unstyled">
                <div class="input-field">
                    <input data-update placeholder="Başlangıç Tarihi" name="start_date" id="start_date" type="date" class="validate" />
                    <span class="helper-text">Bu tarihten itibaren yapılan işlemler.</span>
                </div>
                <div class="input-field">
                    <input data-update placeholder="Bitiş Tarihi" name="end_date" id="end_date" type="date" class="validate" />
                    <span class="helper-text">Bu tarihe kadar yapılmış işlemler.</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-unstyled mb-1">
        <div class="card-content">
            <span class="card-title d-flex">
                <i class="material-icons mr-1">filter_list</i>
                Durum
            </span>
        </div>
        <div class="collection collection-unstyled">
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="status" id="status" type="radio" checked value="" />
                <span>Tümü</span>
            </label>
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="status" id="status-success" type="radio" value="on" />
                <span>Ödenen</span>
            </label>
            <label class="collection-item waves-effect d-block" data-update="true">
                <input name="status" id="status-pending" type="radio" value="off" />
                <span>Ödenmeyen</span>
            </label>
        </div>
    </div>
@endsection

@section('wildcard')
    <div class="card wild-background">
        <div class="container">
            <span class="wildcard-title">Faturalar</span>
        </div>
    </div>
@endsection

@push('local.scripts')
    $(document).on('change', '[data-update]', function() {
        var search = $('#collection');
            search.data('skip', 0).addClass('json-clear');

        vzAjax(search)
    })

    function __collection(__, obj)
    {
        var item_model = __.children('.model');

        if (obj.status == 'ok')
        {
            if (obj.hits.length)
            {
                $.each(obj.hits, function(key, o) {
                    var item = item_model.clone();
                        item.removeClass('model hide')
                            .addClass('_tmp d-flex')
                            .attr('data-id', o.invoice_id);

                        item.find('[data-name-alias=name]').attr('data-id', o.user.id).html(o.user.name)
                        item.find('[data-name=created_at]').html(o.created_at)
                        item.find('[data-name=name]').html('#' + o.invoice_id).attr('href', '{{ url('ayarlar/organizasyon/fatura') }}/' + o.invoice_id)
                        item.find('[data-name=price]').html('{{ config('formal.currency') }} ' + o.total_price)
                        item.find('[data-name=message]').removeClass(o.reason_msg ? 'hide' : '').html(o.reason_msg)
                        item.find('[data-name=method]').removeClass(o.method ? 'hide' : '').html(o.method)
                        item.find('[data-name=status]')
                            .removeClass('red-text green-text')
                            .addClass(o.paid_at ? 'green-text' : 'red-text')
                            .html(o.paid_at ? o.paid_at : 'Ödenmedi')

                        item.appendTo(__)
                })
            }

            $('[data-name=count]').html(obj.total)
        }
    }
@endpush

@section('content')
    <div class="card">
        <div class="card-content">
            <span class="grey-text">Fatura Sayısı</span>
            <span class="card-title" data-name="count">0</span>
        </div>
        <nav class="nav-half">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#collection"
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                </div>
            </div>
        </nav>
        <ul class="collection collection-unstyled load json-clear" 
             id="collection"
             data-href="{{ route('admin.invoices') }}"
             data-method="post"
             data-skip="0"
             data-take="10"
             data-include="string,status,start_date,end_date"
             data-more-button="#collection-more_button"
             data-callback="__collection"
             data-loader="#home-loader"
             data-nothing>
            <li class="collection-item nothing hide">
                @component('components.nothing')@endcomponent
            </li>
            <li class="collection-item justify-content-between model hide">
                <span>
                    <a
                        href="#"
                        class="json"
                        data-href="{{ route('route.generate.id') }}"
                        data-method="post"
                        data-name="admin.user"
                        data-name-alias="name"
                        data-callback="__go"></a>
                    <a href="#" class="d-block grey-text" data-name="name"></a>
                    <small class="hide red-text" data-name="message"></small>
                </span>
                <span class="right-align">
                    <small class="hide" data-name="method"></small>
                    <span class="d-block green-text" data-name="price"></span>
                    <i>
                        <small class="grey-text" data-name="created_at"></small>
                        <small data-name="status"></small>
                    </i>
                </span>
            </li>
        </ul>

        @component('components.loader')
            @slot('color', 'blue-grey')
            @slot('id', 'home-loader')
            @slot('class', 'card-loader-unstyled')
        @endcomponent
    </div>

    <a href="#"
       class="more hide json"
       id="collection-more_button"
       data-json-target="#collection">Daha Fazla</a>
@endsection
