@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Ayarlar'
        ],
        [
            'text' => 'Referans Sistemi'
        ]
    ],
    'dock' => true
])

@section('content')
    <div class="card">
        <div class="card-content teal d-flex justify-content-between">
            <a href="#" class="white-text" data-tooltip="Bakiye" data-position="right">{{ config('formal.currency') }} 0</a>
            <span class="white-text" data-tooltip="Referans Kodu" data-position="right">{{ $user->reference_code }}</span>
            <span class="white-text" data-tooltip="Pay Oranı" data-position="left">{{ config('formal.reference_rate') }}%</span>
        </div>
        @if ($user->reference_code)
            <div class="card-tabs">
                <ul class="tabs tabs-fixed-width tabs-transparent teal">
                    <li class="tab">
                        <a href="#referanslar" class="active waves-effect waves-light">Referanslar</a>
                    </li>
                    <li class="tab">
                        <a href="#islem-gecmisi" class="waves-effect waves-light">İşlem Geçmişi</a>
                    </li>
                </ul>
            </div>
            <div id="referanslar">
                <ul class="collection load json-clear" 
                    id="references"
                    data-href="{{ route('settings.references') }}"
                    data-skip="0"
                    data-take="10"
                    data-more-button="#references-more_button"
                    data-callback="__references"
                    data-method="post"
                    data-nothing>
                    <li class="collection-item nothing hide p-2">
                        @component('components.nothing')
                            @slot('size', 'small')
                            @slot('text', 'Henüz sizin referansınızla kaydolan kimse olmadı!')
                        @endcomponent
                    </li>
                    <li class="collection-item model hide">
                        <div class="d-flex justify-content-between">
                            <p data-name="name"></p>
                            <time class="timeago grey-text"></time>
                        </div>
                    </li>
                </ul>

                @component('components.loader')
                    @slot('color', 'cyan')
                    @slot('class', 'card-loader-unstyled')
                    @slot('id', 'references-loader')
                @endcomponent

                <div class="center-align">
                    <button class="btn-flat waves-effect hide json"
                            id="references-more_button"
                            type="button"
                            data-json-target="ul#references">Daha Fazla</button>
                </div>
            </div>
            <div id="islem-gecmisi" style="display: none;">
                <ul class="collection load json-clear" 
                    id="transactions"
                    data-href="{{ route('settings.transactions') }}"
                    data-skip="0"
                    data-take="10"
                    data-more-button="#transactions-more_button"
                    data-callback="__transactions"
                    data-method="post"
                    data-nothing>
                    <li class="collection-item nothing hide p-2">
                        @component('components.nothing')
                            @slot('size', 'small')
                            @slot('text', 'Henüz sizin referansınızla kaydolan kimse olmadı!')
                        @endcomponent
                    </li>
                    <li class="collection-item model hide">
                        <div class="d-flex justify-content-between">
                            <p data-name="name"></p>
                            <time class="timeago grey-text"></time>
                        </div>
                    </li>
                </ul>

                @component('components.loader')
                    @slot('color', 'cyan')
                    @slot('class', 'card-loader-unstyled')
                    @slot('id', 'transactions-loader')
                @endcomponent

                <div class="center-align">
                    <button class="btn-flat waves-effect hide json"
                            id="transactions-more_button"
                            type="button"
                            data-json-target="ul#transactions">Daha Fazla</button>
                </div>
            </div>

            @push('local.scripts')
                function __references(__, obj)
                {
                    var ul = $('#references');
                    var item_model = ul.children('li.model');

                    if (obj.status == 'ok')
                    {
                        item_model.addClass('hide')

                        if (obj.hits.length)
                        {
                            $.each(obj.hits, function(key, o) {
                                var item = item_model.clone();
                                    item.removeClass('model hide').addClass('_tmp').attr('data-id', 'list-item-' + o.id)

                                    item.find('[data-name=name]').html(o.name)
                                    item.find('time').attr('data-time', o.created_at).html(o.created_at)

                                    item.appendTo(ul)
                            })
                        }

                        $('#references-loader').hide()
                    }
                }

                function __transactions(__, obj)
                {
                    var ul = $('#transactions');
                    var item_model = ul.children('li.model');

                    if (obj.status == 'ok')
                    {
                        item_model.addClass('hide')

                        if (obj.hits.length)
                        {
                            $.each(obj.hits, function(key, o) {
                                var item = item_model.clone();
                                    item.removeClass('model hide').addClass('_tmp').attr('data-id', 'list-item-' + o.id)

                                    item.find('[data-name=name]').html(o.name)
                                    item.find('time').attr('data-time', o.created_at).html(o.created_at)

                                    item.appendTo(ul)
                            })
                        }

                        $('#transactions-loader').hide()
                    }
                }
            @endpush
        @else
            <div class="card-content">
                @component('components.nothing')
                    @slot('text_class', 'grey-text')
                    @slot('sun', 'attach_money')
                    @slot('cloud', 'beach_access')
                    @slot('cloud_class', 'grey-text text-darken-2')
                    @slot('size', 'small')
                    @slot('text', 'Şu an referans sistemine dahil değilsiniz.<br />İstediğiniz zaman referans sitemine dahil olup kazanç ortağımız olabilirsiniz.')
                @endcomponent

                <br />

                <p class="mb-0 teal-text">Sizin referansınızla kaydolan müşterilerimizin gerçekleştirdiği her alışverişten pay oranıda kazanç elde edersiniz.</p>
                <p class="mb-2 teal-text">Elde ettiğiniz her {{ config('formal.currency') }} 100 ve üzeri tutardaki kazançlarınızı dilediğiniz zaman çekebilirsiniz.</p>

                <a
                    href="#"
                    class="btn-flat btn-large waves-effect mx-auto d-table json"
                    data-href="{{ route('settings.reference.start') }}"
                    data-method="post"
                    data-callback="__start">BAŞLAYIN</a>
            </div>

            @push('local.scripts')
                function __start(__, obj)
                {
                    if (obj.status == 'ok')
                    {
                        location.reload()
                    }
                }
            @endpush
        @endif
    </div>
@endsection

@section('dock')
    @include('settings._menu', [ 'active' => 'reference' ])
@endsection

@push('local.scripts')
    $('.tabs').tabs()
@endpush
