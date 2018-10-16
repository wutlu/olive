@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Twitter Bağlantısı'
        ]
    ]
])

@push('local.styles')
.twitter-logo {
    width: 128px;
    height: 128px;

    margin: 1rem auto;
    padding: 1rem;

    display: table;

    background-color: #e1f5fe;

    border-radius: 50%;
}

p {
    padding: 1rem 0;
}

pre {
    margin: 0 0 1rem;
    padding: 1rem;
}
@endpush

@section('content')
    <div class="row">
        <div class="col m8 offset-m2 l6 offset-l3">
            <div class="card">
                <div class="card-image">
                    <img src="{{ asset('img/card-header.jpg') }}" alt="Twitter Bağlantısı" />
                    <span class="card-title">Twitter Bağlantısı</span>
                </div>
                <div class="card-content">
                    @if (session('denied'))
                        <pre class="red-text red lighten-5">Twitter bağlantısı sağlanılamadı.</pre>
                    @endif

                    @if (@$organisation->twitterAccount->status === false)
                        <pre class="red-text red lighten-5">{{ $organisation->twitterAccount->reasons }}.</pre>
                    @endif

                    <div class="center-align">
                        <span class="card-title">{{ $organisation->name }}</span>
                        <form method="post" action="{{ route(@$organisation->twitterAccount->status ? 'twitter.disconnect' : 'twitter.connect.redirect') }}">
                            @csrf
                            <img alt="Twitter" src="{{ $organisation->twitterAccount ? $organisation->twitterAccount->avatar : asset('img/logos/twitter.svg') }}" class="twitter-logo" />
                            @if ($organisation->twitterAccount)
                                <span>{{ $organisation->twitterAccount->name }}</span>
                                <p class="grey-text">{{ $organisation->twitterAccount->screen_name }}</p>
                            @endif

                            <button type="submit" class="btn-flat waves-effect">
                                @if ($organisation->twitterAccount)
                                    {{ $organisation->twitterAccount->status ? 'Bağlantıyı Kes' : 'Tekrar Bağlan' }}
                                @else
                                    {{ 'Bağlan' }}
                                @endif
                            </button>
                        </form>
                    </div>

                    <p class="grey-text">Sizin adınıza veri elde etmek için herhangi bir Twitter hesabınızı organizasyonunuza bağlamanız gerekiyor.</p>
                    <p class="grey-text">Twitter hesabınız ile bilginiz dışında hiçbir işlem gerçekleştirmeyeceğiz.</p>
                    <p class="grey-text">Ayrıca istediğiniz zaman Twitter hesabınızı bizden ayırabilirsiniz.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
