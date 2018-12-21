@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Trend Analizi'
        ],
        [
            'text' => 'Trend Endeksi'
        ]
    ],
    'dock' => true
])

@push('local.scripts')
    $('.tabs').tabs()
@endpush

@section('dock')
    @include('trends._menu', [ 'active' => 'trend_index' ])
@endsection

@section('content')
    <div class="card teal">
        <div class="card-content white-text">
            <p>Oluşturduğunuz/Oluşturacağınız endeksler organizasyonunuza ait olacaktır.</p>
            <p>Oluşturduğunuz endeksler organizasyon üyeleriniz tarafından incelenip güncellenebilir.</p>
            <p>OR, AND, NOR ve (parantez) ifadelerini kullanabilirsiniz.</p>
        </div>
        <nav class="grey darken-4">
            <div class="nav-wrapper">
                <div class="input-field">
                    <input id="string"
                           name="string"
                           type="search"
                           class="validate json json-search"
                           data-json-target="#crawlers"
                           placeholder="Ara" />
                    <label class="label-icon" for="string">
                        <i class="material-icons">search</i>
                    </label>
                    <i class="material-icons">close</i>
                </div>
            </div>
        </nav>
        <div class="card-tabs">
            <ul class="tabs tabs-fixed-width tabs-transparent">
                <li class="tab">
                    <a href="#list" class="waves-effect waves-light active">Endeksleriniz</a>
                </li>
                <li class="tab">
                    <a href="#new" class="waves-effect waves-light">Yeni Endeks</a>
                </li>
            </ul>
        </div>

        <div class="card-content white" id="new" style="display: none;">
            test
        </div>

        <div class="collection white" id="list">
        	<a class="collection-item waves-effect d-flex justify-content-between" href="#">
        		<span>
        			<span class="d-block">Test</span>
        			<time class="grey-text">2018-12-12</time>
        		</span>
        		<span class="grey-text">Ahmet</span>
        	</a>
        </div>
    </div>
@endsection
