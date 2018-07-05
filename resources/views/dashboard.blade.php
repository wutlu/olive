@extends('layouts.app', [
    'sidenav_fixed_layout' => true
])

@section('content')
<div class="row">
    <div class="col xl4 l5 s12">
        @if (@auth()->user()->organisation_id)
        <div class="card">
            <div class="card-image">
                <img src="{{ asset('img/md/21.jpg') }}" alt="" />
                <span class="card-title">{{ $user->organisation->name }}</span>
                <a data-target="organisation-dropdown" class="dropdown-trigger btn-floating btn-large halfway-fab waves-effect waves-teal teal darken-4">
                    <i class="material-icons">more_vert</i>
                </a>

                <ul id="organisation-dropdown" class="dropdown-content">
                    <li>
                        <a href="#" class="waves-effect">
                            <i class="material-icons">create</i>
                            İsmi Güncelle
                        </a>
                    </li>
                    <li>
                        <a href="#" class="waves-effect">
                            <i class="material-icons">person_add</i>
                            Kullanıcı Ekle
                        </a>
                    </li>
                    <li>
                        <a href="#" class="waves-effect">
                            <i class="material-icons">local_bar</i>
                            Ayrıl
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-content">
                <p class="grey-text">{{ count($user->organisation->users) }}/{{ $user->organisation->capacity }} kullanıcı</p>
            </div>
            <div class="collection">
                @foreach ($user->organisation->users as $u)
                <a href="#" class="collection-item avatar waves-effect">
                    <img src="{{ $u->avatar() }}" alt="" class="circle">
                    <span class="title">{{ $u->name }}</span>
                    <p>{{ $u->email }}</p>
                    <p class="grey-text">{{ $u->id == $user->organisation->user_id ? 'Organizasyon Sahibi' : 'Kullanıcı' }}</p>
                </a>
                @endforeach
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-image">
                <img src="{{ asset('img/md/23.jpg') }}" alt="" />
                <span class="card-title">Organizasyon Oluşturun</span>
            </div>
            <div class="card-content">
                <p class="grey-text">Profesyonel bir ortamda tüm modüllerden faydalanabilmek için hemen bir organizasyon oluşturun.</p>
            </div>
            <div class="card-action">
                <a href="{{ route('organisation.create') }}" id="start">Başlayın</a>
            </div>
        </div>

        @if (!auth()->user()->skip_intro && auth()->user()->verified)
            <div class="tap-target teal white-text" data-target="start">
                <div class="tap-target-content">
                    <h5>Organizasyon Oluşturun</h5>
                    <p>Hemen profesyonel olarak başlayın!</p>
                </div>
            </div>

            @push('local.scripts')
            $('.tap-target').tapTarget({
                'onClose': function() {
                    $.ajax({
                        url: '{{ route('intro.skip') }}'
                    });
                }
            });
            $('.tap-target').tapTarget('open');
            @endpush
        @endif
        @endif
    </div>
    <div class="col xl8 l7 s12">
        @push('local.scripts')

        function __activities(__, obj)
        {
            var ul = $('#activities');
            var item_model = ul.children('li.model');

            if (obj.status == 'ok')
            {
                item_model.addClass('d-none')

                if (obj.hits.length)
                {
                    $.each(obj.hits, function(key, o) {
                        var item = item_model.clone();
                            item.removeClass('model d-none').addClass('_tmp').attr('data-id', 'list-item-' + o.id)

                            item.find('.collapsible-header > span > p').html(o.title)
                            item.find('.collapsible-header > span > small').html(o.updated_at)
                            item.find('.collapsible-header > i.icon').html(o.icon)
                            item.find('.collapsible-body > span').html(o.markdown)

                            if (o.markdown_color)
                            {
                                item.find('.collapsible-body').css({ 'background-color': o.markdown_color })
                            }

                            item.appendTo(ul)
                    })
                }
            }
        }

        @endpush
        <ul class="collapsible load json-clear" 
            id="activities"
            data-href="{{ route('dashboard.activities') }}"
            data-skip="0"
            data-take="5"
            data-more-button="#activities-more_button"
            data-callback="__activities"
            data-nothing>
            <li class="nothing d-none">
                <div class="not-found">
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">cloud</i>
                    <i class="material-icons">wb_sunny</i>
                    <p>Aktivite Bulunamadı.</p>
                </div>
            </li>
            <li class="model d-none">
                <div class="collapsible-header">
                    <i class="material-icons icon"></i>
                    <span>
                        <p></p>
                        <small class="grey-text"></small>
                    </span>
                    <i class="material-icons arrow">keyboard_arrow_down</i>
                </div>
                <div class="collapsible-body">
                    <span></span>
                </div>
            </li>
        </ul>
        <div class="center-align">
            <button class="btn-flat waves-effect d-none json"
                    id="activities-more_button"
                    type="button"
                    data-json-target="ul#activities">Daha Fazla</button>
        </div>
    </div>
</div>
@endsection

@push('local.scripts')

    @if (session('validate'))
        M.toast({ html: 'Tebrikler! E-posta adresiniz doğrulandı!', classes: 'green' })
    @endif

@endpush
 