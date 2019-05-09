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
            'text' => '🐞 Gerçek Zamanlı Kelime Grupları'
        ]
    ],
    'dock' => true,
    'footer_hide' => true
])

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Gerçek Zamanlı Kelime Grupları</span>
        </div>
        <ul class="collection">
            @forelse ($organisation->realTimeKeywordGroups as $group)
                <li class="collection-item">
                    <div class="d-flex justify-content-between">
                        <p class="d-table">{{ $group->name }}</p>
                        <time class="timeago grey-text" data-time="{{ $group->created_at }}"></time>
                    </div>
                    <div class="mb-1">
                        <div class="input-field">
                            <textarea
                                id="keywords-{{ $group->id }}"
                                name="keywords-{{ $group->id }}"
                                data-alias="keywords"
                                data-method="post"
                                data-href="{{ route('admin.organisation.keyword_groups.update') }}"
                                data-id="{{ $group->id }}"
                                class="materialize-textarea validate json">{{ $group->keywords }}</textarea>
                            <label for="keywords-{{ $group->id }}">Kelimeler</label>
                            <div class="helper-text"></div>
                        </div>
                    </div>
                    <div>
                        {!! in_array('youtube_video', $group->modules) ? '<span class="chip">YouTube Video</span>' : '' !!}
                        {!! in_array('youtube_comment', $group->modules) ? '<span class="chip">YouTube Yorum</span>' : '' !!}
                        {!! in_array('twitter', $group->modules) ? '<span class="chip">Twitter</span>' : '' !!}
                        {!! in_array('sozluk', $group->modules) ? '<span class="chip">Sözlük</span>' : '' !!}
                        {!! in_array('news', $group->modules) ? '<span class="chip">Haber</span>' : '' !!}
                        {!! in_array('shopping', $group->modules) ? '<span class="chip">E-ticaret</span>' : '' !!}
                    </div>
                </li>
            @empty
                <li class="collection-item">
                    @component('components.nothing')@endcomponent
                </li>
            @endforelse
        </ul>
    </div>
@endsection

@section('dock')
    @include('organisation.admin._menu', [ 'active' => 'groups.keyword', 'id' => $organisation->id ])
@endsection

@push('local.scripts')
    M.textareaAutoResize($('textarea[name=keywords]'))
@endpush
