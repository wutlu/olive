@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Ayarlar',
            'link' => route('settings')
        ],
        [
            'text' => 'Organizasyon Ayarları'
        ]
    ],
    'dock' => 'settings'
])

@if (session('organisation') == 'have')
	@push('local.scripts')
		M.toast({
            html: 'Zaten bir organizasyonunuz mevcut.',
            classes: 'blue'
        })
	@endpush
@endif

@section('content')
<a href="#" data-class="#dock-content" data-class-toggle="active">test</a>
asdsa asd asd asdsadsadasd asdasd ad ad asd asdsad<br />
asdsa asd asd asdsadsadasd asdasd ad ad asd asdsad
asdsa asd asd asdsadsadasd asdasd ad ad asd asdsad
asdsa asd asd asdsadsadasd asdasd ad ad asd asdsad
asdsa asd asd asdsadsadasd asdasd ad ad asd asdsad
asdsa asd asd asdsadsadasd asdasd ad ad asd asdsad
asdsa asd asd asdsadsadasd asdasd ad ad asd asdsad<br />
asdsa asd asd asdsadsadasd asdasd ad ad asd asdsad<br />
asdsa asd asd asdsadsadasd asdasd ad ad asd asdsad<br />
asdsa asd asd asdsadsadasd asdasd ad ad asd asdsad<br />
asdsa asd asd asdsadsadasd asdasd ad ad asd asdsad<br />
asdsa asd asd asdsadsadasd asdasd ad ad asd asdsad<br />
asdsa asd asd asdsadsadasd asdasd ad ad asd asdsad<br />
@endsection
