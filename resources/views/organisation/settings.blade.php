@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Ayarlar'
        ],
        [
            'text' => 'Organizasyon Ayarları'
        ]
    ],
    'dock' => true
])

@if (session('organisation') == 'have')
	@push('local.scripts')
		M.toast({
            html: 'Zaten bir organizasyonunuz mevcut.',
            classes: 'blue'
        })
	@endpush
@endif

@push('local.scripts')

$(document).on('click', 'a.name-change', function() {
    var mdl = modal({
            'id': 'detail',
            'body': $('<div />', {
                'class': 'input-field',
                'html': [
                    $('<input />', {
                        'id': 'organisation_name',
                        'name': 'organisation_name',
                        'type': 'text',
                        'class': 'validate',
                        'val': '{{ $user->organisation->name }}',
                        'data-length': 16
                    }),
                    $('<label />', {
                        'for': 'organisation_name',
                        'html': 'Organizasyon Adı'
                    }),
                    $('<span />', {
                        'class': 'helper-text'
                    })
                ]
            }),
            'size': 'modal-medium',
            'title': 'Ad Değiştir',
            'options': {
                dismissible: false
            }
        });

        mdl.find('.modal-footer')
           .html([
               $('<a />', {
                   'href': '#',
                   'class': 'modal-close waves-effect btn-flat',
                   'html': buttons.cancel
               }),
               $('<span />', {
                   'html': ' '
               }),
               $('<a />', {
                   'href': '#',
                   'class': 'waves-effect btn json',
                   'data-href': '{{ route('organisation.update.name') }}',
                   'data-method': 'patch',
                   'data-include': 'organisation_name',
                   'data-callback': '__update__organisation_name',
                   'html': buttons.update
               })
           ])

    M.updateTextFields()

    $('input[name=organisation_name]').characterCounter()
})

function __update__organisation_name(__, obj)
{
    if (obj.status == 'ok')
    {
        var name = $('#organisation_name').val();

        $('#organisation-card').find('span.card-title').children('span').html(name)

        $('#modal-detail').modal('close')

        M.toast({
            html: 'Organizasyon adı güncellendi.',
            classes: 'green darken-2'
        })
    }
}

@endpush

@section('content')
<div class="card" id="organisation-card">
    <div class="card-content">
        <span class="card-title">
            <span>{{ $user->organisation->name }}</span>
            <a class="name-change material-icons" href="#">create</a>
        </span>
    </div>

    <div class="card-tabs">
        <ul class="tabs tabs-fixed-width">
            <li class="tab">
                <a href="#tab-1">
                    <i class="material-icons">people</i>
                </a>
            </li>
            <li class="tab">
                <a href="#tab-2">
                    <i class="material-icons">tune</i>
                </a>
            </li>
            <li class="tab">
                <a href="#tab-3">
                    <i class="material-icons">settings</i>
                </a>
            </li>
        </ul>
    </div>
    <div id="tab-1" class="card-content grey lighten-4">
        <div class="card-content">
            <p class="grey-text">
                {{ count($user->organisation->users) }}/{{ $user->organisation->capacity }} kullanıcı
            </p>
        </div>

        <ul class="collection">
            @foreach ($user->organisation->users as $u)
            <li class="collection-item avatar">
                <img src="{{ $u->avatar() }}" alt="avatar" class="circle">
                <span class="title">{{ $u->name }}</span>
                <p class="grey-text">{{ $u->email }}</p>
                <p class="grey-text">{{ $u->id == $user->organisation->user_id ? 'Organizasyon Sahibi' : 'Kullanıcı' }}</p>
                <a href="#" class="secondary-content dropdown-trigger" data-target="dropdown-user-{{ $u->id }}">
                    <i class="material-icons">more_vert</i>
                </a>

                <ul id="dropdown-user-{{ $u->id }}" class="dropdown-content">
                    <li>
                        <a href="#">
                            <i class="material-icons">delete_forever</i> Çıkar
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="material-icons">fingerprint</i> Devret
                        </a>
                    </li>
                </ul>
            </li>
            @endforeach
        </ul>
        @if (count($user->organisation->users) < $user->organisation->capacity)
        <div class="input-field teal-text">
            <input name="email" id="email" type="email" class="validate" />
            <label for="email">E-posta</label>
            <small class="helper-text">Gireceğiniz e-posta adresine organizasyon daveti e-posta yoluyla gönderilir.</small>
        </div>
        <a href="#" class="waves-effect waves-dark btn-small">Davet Et</a>
        @endif
    </div>
    <div id="tab-2" class="card-content grey lighten-4">
        yükselt,
        toplam ödenen
    </div>
    <div id="tab-3" class="card-content grey lighten-4">
        @if ($user->organisation->user_id == $user->id)
        <div class="d-flex justify-content-between">
            <span>
                <strong>Organizasyonu Silin</strong>
                <p class="grey-text">- Organizasyona ait tüm etkinlikler kalıcı olarak silinir.</p>
                <p class="grey-text">- Organizasyona dahil tüm kullanıcıların organizasyon bağlantıları kaldırılır.</p>
                <p class="grey-text">- Ücret iadesi alamazsınız.</p>
            </span>
            <a href="#" class="btn red darken-1 waves-effect" data-button="__delete">Sil</a>
        </div>

        @php
        $key = 'organizasyonu silmek istiyorum';
        @endphp

        @push('local.scripts')
        $(document).on('click', '[data-button=__delete]', function() {
            var mdl = modal({
                    'id': 'delete',
                    'body': [
                        $('<p />', {
                            'html': 'Organizasyonu silmek için aşağıdaki alana küçük harflerle "{{ $key }}" yazmalısınız.'
                        }),
                        $('<p />', {
                            'html': 'Bu işlem geri alınamaz.',
                            'class': 'red-text'
                        }),
                        $('<div />', {
                            'class': 'input-field',
                            'html': [
                                $('<input />', {
                                    'id': 'delete_key',
                                    'name': 'delete_key',
                                    'type': 'text',
                                    'class': 'validate',
                                    'pattern': '^\{{ $key }}$'
                                }),
                                $('<span />', {
                                    'class': 'helper-text',
                                    'html': 'Organizasyonu silmek için gerekli kelimeleri girin.'
                                })
                            ]
                        })
                    ],
                    'size': 'modal-small',
                    'title': 'Ayrıl',
                    'options': {}
                });

                M.updateTextFields()

                mdl.find('.modal-footer')
                   .html([
                       $('<a />', {
                           'href': '#',
                           'class': 'modal-close waves-effect btn-flat',
                           'html': buttons.cancel
                       }),
                       $('<span />', {
                           'html': ' '
                       }),
                       $('<a />', {
                           'href': '#',
                           'class': 'waves-effect btn red darken-4 json',
                           'data-href': '{{ route('settings.organisation.delete') }}',
                           'data-include': 'delete_key',
                           'data-method': 'post',
                           'data-callback': '__delete',
                           'html': buttons.ok
                       })
                   ])
        })

        function __delete(__, obj)
        {
            var delete_key_input = $('input[name=delete_key]');

            if (delete_key_input.val() == '{{ $key }}')
            {
                if (obj.status == 'ok')
                {
                    $('#modal-delete').modal('close')

                    setTimeout(function() {
                        window.location.href = '{{ route('dashboard') }}';
                    }, 400)
                }
                else if (obj.status == 'owner')
                {
                    M.toast({
                        html: 'Organizasyon sahibi değilken organizasyonu silemezsiniz.',
                        classes: 'yellow darken-2'
                    })
                }
            }
            else
            {
                M.toast({
                    html: 'Onay alanı geçerli değil.',
                    classes: 'red darken-2'
                })
            }
        }
        @endpush

        @else
        <div class="d-flex justify-content-between">
            <span>
                <strong>Organizasyondan Ayrılın</strong>
                <p class="grey-text">Organizasyondan ayrıldıktan sonra yeni bir davet ile tekrar katılabilirsiniz.</p>
            </span>
            <a href="#" class="btn red darken-1 waves-effect" data-button="__leave">Ayrıl</a>
        </div>

        @php
        $key = 'organizasyondan ayrılmak istiyorum';
        @endphp

        @push('local.scripts')
        $(document).on('click', '[data-button=__leave]', function() {
            var mdl = modal({
                    'id': 'leave',
                    'body': [
                        $('<p />', {
                            'html': 'Organizasyondan ayrılmak için aşağıdaki alana küçük harflerle "{{ $key }}" yazmalısınız.'
                        }),
                        $('<p />', {
                            'html': 'Bu işlem geri alınamaz.',
                            'class': 'red-text'
                        }),
                        $('<div />', {
                            'class': 'input-field',
                            'html': [
                                $('<input />', {
                                    'id': 'leave_key',
                                    'name': 'leave_key',
                                    'type': 'text',
                                    'class': 'validate',
                                    'pattern': '^\{{ $key }}$'
                                }),
                                $('<span />', {
                                    'class': 'helper-text',
                                    'html': 'Organizasyondan ayrılmak için gerekli kelimeleri girin.'
                                })
                            ]
                        })
                    ],
                    'size': 'modal-small',
                    'title': 'Ayrıl',
                    'options': {}
                });

                M.updateTextFields()

                mdl.find('.modal-footer')
                   .html([
                       $('<a />', {
                           'href': '#',
                           'class': 'modal-close waves-effect btn-flat',
                           'html': buttons.cancel
                       }),
                       $('<span />', {
                           'html': ' '
                       }),
                       $('<a />', {
                           'href': '#',
                           'class': 'waves-effect btn red darken-4 json',
                           'data-href': '{{ route('settings.organisation.leave') }}',
                           'data-include': 'leave_key',
                           'data-method': 'post',
                           'data-callback': '__leave',
                           'html': buttons.ok
                       })
                   ])
        })

        function __leave(__, obj)
        {
            var leave_key_input = $('input[name=leave_key]');

            if (leave_key_input.val() == '{{ $key }}')
            {
                if (obj.status == 'ok')
                {
                    $('#modal-leave').modal('close')

                    setTimeout(function() {
                        window.location.href = '{{ route('dashboard') }}';
                    }, 400)
                }
                else if (obj.status == 'owner')
                {
                    M.toast({
                        html: 'Organizasyon sahibiyken organizasyondan ayrılamazsınız.',
                        classes: 'yellow darken-2'
                    })
                }
            }
            else
            {
                M.toast({
                    html: 'Onay alanı geçerli değil.',
                    classes: 'red darken-2'
                })
            }
        }
        @endpush
        @endif
    </div>
</div>
@endsection

@section('dock')
    @include('layouts.dock.settings', [ 'active' => 'organisation' ])
@endsection

@push('local.scripts')

$('.tabs').tabs()
$('a.secondary-content').dropdown({
    alignment: 'right'
})

@endpush
