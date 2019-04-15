@extends('layouts.app')

@section('title', 'Kaynaklar')

@push('local.styles')
    body {
        background-color: #f0f0f0;
    }
@endpush

@section('content')
    <div class="navbar-fixed">
        <nav class="white">
            <a href="{{ route('dashboard') }}" class="brand-logo center">
                <img alt="{{ config('app.name') }}" src="{{ asset('img/olive_logo.svg') }}" />
            </a>
        </nav>
    </div>
    <div class="container pt-2 pb-2">
        <div class="card teal darken-2">
            <div class="card-content">
                <span class="card-title white-text">Kaynaklar</span>
                <p class="white-text">- Tüm kaynaklar açık kaynak olup, yasal olarak kaynak belirtilerek kullanılmaktadır.</p>
                <p class="white-text">- Diğer hukuksal bilgilere <a href="{{ route('page.view', 'gizlilik-politikasi') }}">Gizlilik Politikası</a> ve <a href="{{ route('page.view', 'kullanim-kosullari') }}">Kullanım Koşulları</a> sayfalarından ulaşabilirsiniz.</p>
                <p class="white-text">- Yerel veya farklı kaynak istekleriniz, <a href="{{ route('settings.support', 'kaynak-istegi') }}">DESTEK</a> bölümünden bize bildirebilirsiniz.</p>
            </div>
            <ul class="tabs tabs-transparent">
                <li class="tab">
                    <a href="#media" class="active">Medya</a>
                </li>
                <li class="tab">
                    <a href="#shopping">E-Ticaret</a>
                </li>
                <li class="tab">
                    <a href="#social">Sosyal Medya</a>
                </li>
                <li class="tab">
                    <a href="#blog">Blog</a>
                </li>
                <li class="tab">
                    <a href="#forum">Forum</a>
                </li>
            </ul>
            <div id="media" class="collection max-height white">
                @forelse ($media as $key => $m)
                    <a href="{{ $m->site }}" target="_blank" class="collection-item d-flex justify-content-end">
                        <span class="mr-auto">{{ ($key+1).' - '.$m->name }}</span>
                        <span class="badge teal white-text">{{ $m->id }}</span> 

                        @if ($m->status)
                            <span class="badge green white-text">Aktif</span>
                        @else
                            <span class="badge red white-text">Pasif</span>
                        @endif
                    </a>
                @empty
                    <div class="collection-item grey-text">Şu an için aktif kaynak bulunmuyor.</div>
                @endforelse
            </div>
            <div id="shopping" class="collection max-height white" style="display: none;">
                @forelse ($shopping as $key => $s)
                    <a href="{{ $s->site }}" target="_blank" class="collection-item d-flex justify-content-end">
                        <span class="mr-auto">{{ ($key+1).' - '.$s->name }}</span>
                        <span class="badge teal white-text">{{ $s->id }}</span> 

                        @if ($s->status)
                            <span class="badge green white-text">Aktif</span>
                        @else
                            <span class="badge red white-text">Pasif</span>
                        @endif
                    </a>
                @empty
                    <div class="collection-item grey-text">Şu an için aktif kaynak bulunmuyor.</div>
                @endforelse
            </div>
            <div id="social" class="collection max-height white" style="display: none;">
                <a href="https://www.facebook.com" target="_blank" class="collection-item">
                    Facebook
                    <span class="badge red white-text">Pasif</span>
                </a>
                <a href="https://www.instagram.com" target="_blank" class="collection-item">
                    Instagram
                    <span class="badge red white-text">Pasif</span>
                </a>
                <a href="https://twitter.com" target="_blank" class="collection-item">
                    Twitter

                    @if ($options['twitter.status'] == 'on')
                        <span class="badge green white-text">Aktif</span>
                    @else
                        <span class="badge red white-text">Pasif</span>
                    @endif
                </a>
                <a href="https://www.youtube.com" target="_blank" class="collection-item">
                    YouTube

                    @if ($options['youtube.status'] == 'on')
                        <span class="badge green white-text">Aktif</span>
                    @else
                        <span class="badge red white-text">Pasif</span>
                    @endif
                </a>
                <a href="https://www.google.com" target="_blank" class="collection-item">
                    Google

                    @if ($options['trend.status.google'] == 'on')
                        <span class="badge green white-text">Aktif</span>
                    @else
                        <span class="badge red white-text">Pasif</span>
                    @endif
                </a>

                @forelse ($sozluk as $s)
                    <a href="{{ $s->site }}" target="_blank" class="collection-item d-flex justify-content-end">
                        <span class="mr-auto">{{ $s->name }}</span>
                        <span class="badge teal white-text">{{ $s->id }}</span> 

                        @if ($s->status)
                            <span class="badge green white-text">Aktif</span>
                        @else
                            <span class="badge red white-text">Pasif</span>
                        @endif
                    </a>
                @empty
                    <div class="collection-item grey-text">Şu an için aktif kaynak bulunmuyor.</div>
                @endforelse
            </div>
            <div id="blog" class="collection max-height white" style="display: none;">
                <div class="collection-item grey-text">Şu an için aktif kaynak bulunmuyor.</div>
            </div>
            <div id="forum" class="collection max-height white" style="display: none;">
                <div class="collection-item grey-text">Şu an için aktif kaynak bulunmuyor.</div>
            </div>
        </div>
    </div>
@endsection

@push('local.scripts')
    $(document).ready(function() {
        $('.tabs').tabs()
    })
@endpush
