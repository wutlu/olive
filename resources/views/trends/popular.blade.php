@extends('layouts.app', [
    'sidenav_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Trend Analizi'
        ],
        [
            'text' => 'Popüler Kaynaklar'
        ]
    ],
    'footer_hide' => true,
    'dock' => true,
    'wide' => true
])

@push('local.scripts')
    $('select').formSelect()
@endpush

@section('dock')
    @include('trends._menu', [ 'active' => 'popular' ])

    <form method="get" action="{{ route('trend.popular') }}">
        <div class="card card-unstyled">
            <div class="card-content">
                <div class="input-field">
                    <select name="sort" id="sort">
                        <option value="trend_hit" {{ $request->sort == 'trend_hit' || $request->sort == '' ? 'selected' : '' }}>Trend'e Giriş</option>
                        <option value="exp_trend_hit" {{ $request->sort == 'exp_trend_hit' ? 'selected' : '' }}>Trend'e Ani Giriş</option>
                    </select>
                    <label for="module">Sıralama</label>
                </div>
            </div>
            <div class="card-content">
                <div class="input-field">
                    <select name="module" id="module">
                        <option value="" {{ $request->module ? '' : 'selected' }}>Tümü</option>
                        @foreach ($modules as $key => $module)
                            <option value="{{ $key }}" data-icon="{{ $module['icon'] }}" {{ $request->module == $key ? 'selected' : '' }}>{{ $module['name'] }}</option>
                        @endforeach
                    </select>
                    <label for="module">Modül</label>
                </div>
            </div>
            <div class="card-content">
                <button type="submit" class="btn blue-grey waves-effect">Süz</button>
            </div>
        </div>
    </form>
@endsection

@section('wildcard')
    <div class="card wild-background">
        <div class="pl-1">
            <span class="wildcard-title">Popüler Kaynaklar</span>
        </div>
    </div>
@endsection

@section('content')
    <div class="card card-unstyled">
        @if (count($data))
            <ul class="collection collection-unstyled collection-hoverable">
                @foreach ($data as $key=> $item)
                    <li class="collection-item d-flex justify-content-start">
                        <img align="{{ $item->module }}" src="{{ $modules[$item->module]['icon'] }}" style="min-width: 24px; height: 24px;" class="align-self-center mr-1" />
                        <small class="center-align align-self-center mr-1 rounded blue-grey white-text" style="min-width: 48px; line-height: 48px; white-space: nowrap;">{{ number_format((($data->currentPage() * $pager) + ($key+1)) - $pager) }}</small>
                        <small class="hide-on-med-and-down center-align align-self-center mr-1" style="min-width: 48px; line-height: 16px;">
                            {{ number_format($item->trend_hit) }}
                            <span class="d-block">trend</span>
                        </small>
                        <small class="hide-on-med-and-down center-align align-self-center mr-1" style="min-width: 48px; line-height: 16px;">
                            {{ number_format($item->exp_trend_hit) }}
                            <span class="d-block">hit trend</span>
                        </small>
                        <span class="align-self-center">
                            @if ($item->module == 'twitter_tweet')
                                <span class="d-flex">
                                    <img align="{{ $item->details['name'] }}" src="{{ $item->details['image'] }}" style="width: 32px; height: 32px;" class="align-self-center circle mr-1" />
                                    <span class="align-self-center">
                                        <span class="d-flex">
                                            {{ $item->details['name'] }}

                                            @if ($item->details['verified'] == true)
                                                <i class="material-icons blue-text align-self-center ml-1">check</i>
                                            @endif
                                        </span>
                                        <a target="_blank" href="https://twitter.com/intent/user?user_id={{ $item->details['id'] }}">{{ '@'.$item->details['screen_name'] }}</a> 
                                    </span>
                                </span>
                            @elseif ($item->module == 'youtube_video')
                                <span class="d-flex">
                                    <span class="align-self-center">
                                        <a target="_blank" href="https://www.youtube.com/channel/{{ $item->details['id'] }}">{{ $item->details['title'] }}</a> 
                                    </span>
                                </span>
                            @elseif ($item->module == 'google')
                                <span class="d-flex">
                                    <span class="align-self-center">
                                        <a target="_blank" href="https://www.google.com/search?q={{ $item->details['title'] }}">{{ $item->details['title'] }}</a> 
                                    </span>
                                </span>
                            @elseif ($item->module == 'instagram_hashtag')
                                <span class="d-flex">
                                    <span class="align-self-center">
                                        <a target="_blank" href="https://www.instagram.com/explore/tags/{{ $item->details['title'] }}/">{{ $item->details['title'] }}</a> 
                                    </span>
                                </span>
                            @elseif ($item->module == 'twitter_hashtag')
                                <span class="d-flex">
                                    <span class="align-self-center">
                                        <a target="_blank" href="https://twitter.com/search?q={{ $item->details['title'] }}">{{ $item->details['title'] }}</a> 
                                    </span>
                                </span>
                            @else
                                <span class="d-flex">
                                    <span class="align-self-center">{{ $item->details['title'] }}</span>
                                </span>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="card-content">
                @component('components.nothing')@endcomponent
            </div>
        @endif

        @if ($data->total() > $pager)
            <span class="d-table mx-auto">{!! $data->appends([
                'module' => $request->module,
                'sort' => $request->sort
            ])->links('vendor.pagination.materializecss') !!}</span>
        @endif
    </div>
@endsection
