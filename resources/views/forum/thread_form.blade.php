@php
    $arr = [
        'sidenav_fixed_layout' => true,
        'breadcrumb' => [
            [
                'text' => 'Forum',
                'link' => route('forum.index')
            ]
        ]
    ];

    if ($thread)
    {
        $arr['breadcrumb'][] = [
            'text' => '...',
            'link' => route('forum.category', $thread->category->slug)
        ];
        $arr['breadcrumb'][] = [
            'text' => '...',
            'link' => $thread->route()
        ];
    }
    else
    {
        $arr['dock'] = true;
    }

    $arr['breadcrumb'][] = [
        'text' => $thread ? 'Konu Güncelle' : 'Konu Başlat'
    ];
@endphp

@extends('layouts.app', $arr)

@section('wildcard')
    <div class="card wild-background">
        <div class="card-image">
            <a href="{{ $thread ? $thread->route() : route('forum.index') }}" class="btn-floating btn-large halfway-fab waves-effect teal" data-tooltip="Vazgeç" data-position="left">
                <i class="material-icons">close</i>
            </a>
        </div>
        <div class="container">
            <span class="wildcard-title white-text">{{ $thread ? 'Konu Güncelle' : 'Konu Başlat' }}</span>
        </div>
    </div>
@endsection

@section('content')
    <form id="thread-form" data-include="{{ $thread ? '' : 'category_id' }}" class="json" action="{{ route('forum.thread.form') }}" data-method="post" data-callback="__submit">
        @if ($thread)
            <input type="hidden" name="id" id="id" value="{{ $thread->id }}" />
            <input type="hidden" name="category_id" id="category_id" value="{{ $thread->category_id }}" />
        @endif
        <div class="card">
            @if (@$thread->closed)
                <div class="card-content">
                    @component('components.nothing')
                        @slot('cloud', 'cloud_off')
                        @slot('sun', 'sentiment_very_dissatisfied')
                        @slot('text', 'İlgili konu kapandığından, güncelleme yapamazsınız!')
                        @slot('text_class', 'red-text')
                    @endcomponent
                </div>
            @endif
            <div class="card-content">
                <div class="input-field">
                    <input id="subject" name="subject" type="text" class="validate" data-length="64" value="{{ @$thread->subject }}" />
                    <label for="subject">Konu Başlığı</label>
                </div>
                @if (!$thread)
                    <div class="switch d-table mx-auto">
                        <label>
                            Normal Konu
                            <input type="checkbox" value="on" name="question" id="question" />
                            <span class="lever"></span>
                            Soru Konusu
                        </label>
                    </div>
                @endif
            </div>
            <div class="card-tabs">
                <ul class="tabs">
                    <li class="tab">
                        <a href="#textarea" class="active">Konu İçeriği</a>
                    </li>
                    <li class="tab">
                        <a href="#preview">Önizleme</a>
                    </li>
                </ul>
            </div>
            <div class="card-content textarea-content" id="textarea">
                <div class="input-field">
                    <textarea id="body" name="body" class="materialize-textarea validate" data-length="5000">{{ @$thread->body }}</textarea>
                    <label for="body">Konu İçeriği</label>
                    <div class="helper-text"></div>
                    <small class="grey-text">Bu alanda <a href="https://guides.github.com/features/mastering-markdown/" target="_blank">Markdown</a> kullanabilirsiniz.</small>
                </div>
            </div>
            <div
                class="card-content"
                id="preview"
                data-href="{{ route('forum.message.preview') }}"
                data-method="post"
                data-include="body"
                data-callback="__preview"
                style="display: none;">
                <div class="markdown"></div>
            </div>
            <div class="card-action right-align">
                <button type="submit" class="btn-flat waves-effect">{{ $thread ? 'Güncelle' : 'Başlat' }}</button>
            </div>
        </div>
    </form>
@endsection

@if (!$thread)
    @section('dock')
        <div class="card">
            <div class="card-content teal">
                <span class="card-title white-text mb-0">Kategori</span>
            </div>
            <div class="collection">
                @forelse ($categories as $category)
                    <label class="collection-item waves-effect d-block">
                        <input name="category_id" id="category_id" value="{{ $category->id }}" type="radio" {{ @$thread->category_id == $category->id ? 'checked' : '' }} />
                        <span>{{ $category->name }}</span>
                    </label>
                @empty
                    <div class="collection-item">
                        @component('components.nothing')
                            @slot('text', 'Henüz Kategori Oluşturulmadı.')
                        @endcomponent
                    </div>
                @endforelse
            </div>
        </div>
    @endsection
@endif

@push('external.include.header')
    <link rel="stylesheet" href="{{ asset('css/highlight.min.css?v='.config('app.version')) }}" />
@endpush

@push('external.include.footer')
    <script src="{{ asset('js/highlight.min.js?v='.config('app.version')) }}"></script>
@endpush

@push('local.scripts')
    function __submit(__, obj)
    {
        if (obj.status == 'ok')
        {
            window.location = obj.data.route;
        }
    }

    function __preview(__, obj)
    {
        if (obj.status == 'ok')
        {
            __.children('.markdown').html(obj.data.message)

            $('code').each(function(i, block) {
                hljs.highlightBlock(block);
            })
        }
    }

    $(document).ready(function() {
        $('.tabs').tabs({
            onShow: function(e) {
                if (e.id == 'preview')
                {
                    vzAjax($('#preview'))
                }
            }
        })

        $('input[name=subject], textarea[name=body]').characterCounter()
    })
@endpush
