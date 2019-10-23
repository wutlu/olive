@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Organizasyonlar',
            'link' => route('admin.organisation.list')
        ],
        [
            'text' => $organisation->name,
            'link' => route('admin.organisation', $organisation->id)
        ],
        [
            'text' => '🐞 Kayıtlı Aramalar'
        ]
    ],
    'dock' => true,
    'footer_hide' => true
])

@section('content')
    <div class="card with-bg">
        <div class="card-image">
            <img src="{{ asset('img/md-s/21.jpg') }}" alt="Image" />
            <span class="card-title">Kayıtlı Aramalar</span>
            <a href="{{ route('admin.organisation.saved_search', $organisation->id) }}" class="btn-floating btn-large halfway-fab waves-effect white">
                <i class="material-icons grey-text text-darken-2">add</i>
            </a>
        </div>
        @if ($organisation->savedSearches->count())
            <ul class="collection collection-hoverable">
                @foreach($organisation->savedSearches as $search)
                <li class="collection-item">
                    <span class="d-flex justify-content-between">
                        <span>
                            <span class="d-flex">
                                <span class="mr-1">{{ $search->name }}</span>
                                <span class="grey-text">{{ date('d.m.Y H:i', strtotime($search->created_at)) }}</span>
                            </span>
                            <div class="d-block mt-1"> 
                                @foreach ($search->modules as $module)
                                    <span class="chip">{{ config('system.modules')[$module] }}</span>
                                @endforeach
                            </div>
                        </span>
                        <a class="btn-floating btn-flat waves-effect align-self-center" href="{{ route('admin.organisation.saved_search', [ 'id' => $search->organisation_id, 'search_id' => $search->id ]) }}">
                            <i class="material-icons">create</i>
                        </a>
                    </span>
                </li>
                @endforeach
            </ul>
        @else
            <div class="card-content">
                @component('components.nothing')@endcomponent
            </div>
        @endif
    </div>
@endsection

@section('dock')
    @include('organisation.admin._menu', [ 'active' => 'saved.searches', 'id' => $organisation->id ])
@endsection
