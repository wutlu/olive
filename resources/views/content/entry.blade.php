@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => $title
        ]
    ]
])

@section('wildcard')
	<div class="z-depth-2">
		<div class="container">
			test
		</div>
	</div>
@endsection

@section('content')

@endsection
