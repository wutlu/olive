@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Kaynak Tercihleri'
        ]
    ],
    'footer_hide' => true
])

@section('content')
    <div class="card mb-1">
        <div class="card-image">
            <img src="{{ asset('img/md-s/21.jpg') }}" alt="Card" />
            <span class="card-title">Kaynak Tercihleri</span>
            <a href="{{ route('sources.form') }}" class="btn-floating btn-large halfway-fab waves-effect white">
                <i class="material-icons grey-text text-darken-2">add</i>
            </a>
        </div>
        <div class="card-content">
            <p class="grey-text text-darken-2">{{ $query->total().'/'.$user->organisation->source_limit }}</p>
            @if (!count($query))
                @component('components.nothing')@endcomponent
            @endif
        </div>
        @if (count($query))
            <div class="collection collection-unstyled">
                @foreach ($query as $row)
                    <a href="{{ route('sources.form', $row->id) }}" class="collection-item greyhover waves-effect d-flex flex-wrap justify-content-start">
                        <span class="align-self-center badge green-text">[s:{{ $row->id }}]</span>
                        <span class="align-self-center">{{ $row->name }}</span>
                        <div class="d-flex ml-auto">
                            <img class="align-self-center" alt="Haber" src="{{ asset('img/logos/news.svg') }}" style="width: 24px; height: 24px;" />
                            <span class="align-self-center" style="padding: 4px 10px;">{{ count($row->source_media) }}</span>
                            <img class="align-self-center" alt="Blog" src="{{ asset('img/logos/blog.svg') }}" style="width: 24px; height: 24px;" />
                            <span class="align-self-center" style="padding: 4px 10px;">{{ count($row->source_blog) }}</span>
                            <img class="align-self-center" alt="Forum" src="{{ asset('img/logos/forum.svg') }}" style="width: 24px; height: 24px;" />
                            <span class="align-self-center" style="padding: 4px 10px;">{{ count($row->source_forum) }}</span>
                            <img class="align-self-center" alt="Sahibinden" src="{{ asset('img/logos/sahibinden.svg') }}" style="width: 24px; height: 24px;" />
                            <span class="align-self-center" style="padding: 4px 10px;">{{ count($row->source_shopping) }}</span>
                            <img class="align-self-center" alt="Sahibinden" src="{{ asset('img/logos/eksi.svg') }}" style="width: 24px; height: 24px;" />
                            <span class="align-self-center" style="padding: 4px 10px;">{{ count($row->source_sozluk) }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
    {!! $query->links('vendor.pagination.materializecss') !!}

    <div class="card card-unstyled">
        <div class="card-content grey-text text-darken-2">
            @component('components.alert')
                @slot('text', 'Sorgu alanlarına <span class="grey darken-2 white-text">[s:{kaynak_tercihi_numarası}]</span> yazarak da, ilgili kaynak tercihini aramalarınıza dahil edebilirsiniz.')
                @slot('icon', 'info')
            @endcomponent
        </div>
    </div>
@endsection
