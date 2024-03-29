@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Kullanıcılar',
            'link' => route('admin.user.list')
        ],
        [
            'text' => '🐞 '.$user->name
        ]
    ],
    'dock' => true,
    'footer_hide' => true
])

@push('local.scripts')
    function __account(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('input[name=password]').val('')
            $('input[name=avatar]').prop('checked', false)

            $('img.user-avatar').attr('src', obj.data.avatar ? '{{ url('/') }}/' + obj.data.avatar : '{{ asset('img/icons/people.svg') }}')

            M.toast({
                html: 'Kullanıcı Bilgileri Güncellendi',
                classes: 'green darken-2'
            })
        }
    }
@endpush

@section('content')
    <form
        method="post"
        action="{{ route('admin.user', $user->id) }}"
        class="json"
        id="details-form"
        data-id="{{ $user->id }}"
        data-callback="__account">
        <div class="card blue-grey darken-4 mb-1">
            <div class="card-content">
                <span class="card-title white-text">Referans Bilgileri</span>
                <p class="grey-text">
                    @if ($user->reference)
                        <a class="yellow-text" href="{{ route('admin.user', $user->reference->id) }}">{{ $user->reference->name }}</a> tarafından üye yapıldı.
                    @else
                        Referanssız Üyelik
                    @endif
                </p>
                @if ($user->subUsers->count())
                    <span class="card-title white-text">Referans Olduğu Kullanıcılar ({{ $user->subUsers->count() }})</span>
                    <ul class="collection collection-hoverable collection-unstyled">
                        @foreach ($user->subUsers as $key => $sub)
                            <li class="collection-item d-flex justify-content-end">
                                <span class="d-flex flex-column mr-auto align-self-center">
                                    <a class="grey-text" href="{{ route('admin.user', $sub->id) }}">{{ $sub->name }}</a>
                                    <span class="grey-text text-lighten-2">{{ $sub->email }}</span>
                                </span>
                                <span class="d-flex flex-column right-align align-self-center"> 
                                    @if ($sub->sub_partner_percent)
                                        <span class="white-text ml-1">% {{ $sub->sub_partner_percent }} alt partner</span>
                                    @endif
                                    <span class="badge white ml-1">{{ $sub->subUsers->count() }} alt kullanıcı</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <span class="card-title">Kullanıcı Bilgileri</span>
            </div>
            <ul class="item-group mt-0 mb-0">
                <li class="item p-1">
                    <small class="grey-text">Oluşturuldu</small>
                    <p class="d-block">{{ date('d.m.Y H:i', strtotime($user->created_at)) }}</p>
                </li>
                <li class="item p-1">
                    <small class="grey-text">Güncellendi</small>
                    <p class="d-block">{{ date('d.m.Y H:i', strtotime($user->created_at)) }}</p>
                </li>
            </ul>
            <div class="collection collection-unstyled grey lighten-4">
                <div class="collection-item">
                    <div class="d-flex flex-wrap">
                        <label class="item flex-fill">
                            <input name="partner" type="radio" {{ $user->partner ? '' : 'checked' }} value="" />
                            <span>Partner Değil</span>
                        </label>
                        <label class="item flex-fill">
                            <input name="partner" type="radio" {{ $user->partner == 'eagle' ? 'checked' : '' }} value="eagle" />
                            <span>Eagle Partner</span>
                        </label>
                        <label class="item flex-fill">
                            <input name="partner" type="radio" {{ $user->partner == 'phoenix' ? 'checked' : '' }} value="phoenix" />
                            <span>Phoenix Partner</span>
                        </label>
                        <label class="item flex-fill">
                            <input name="partner" type="radio" {{ $user->partner == 'gryphon' ? 'checked' : '' }} value="gryphon" />
                            <span>Gryphon Partner</span>
                        </label>
                        <label class="item flex-fill">
                            <input name="partner" type="radio" {{ $user->partner == 'dragon' ? 'checked' : '' }} value="dragon" />
                            <span>Dragon Partner</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="collection collection-unstyled">
                <div class="collection-item">
                    <div class="input-field">
                        <input name="name" id="name" value="{{ $user->name }}" type="text" class="validate" autocomplete="off" />
                        <label for="name">Ad</label>
                        <small class="helper-text">Kullanıcı Adı</small>
                    </div>
                </div>
                <div class="collection-item">
                    <div class="input-field">
                        <textarea name="about" id="about" data-length="10000" class="materialize-textarea validate">{{ $user->about }}</textarea>
                        <label for="about">Hakkında</label>
                        <small class="grey-text">Bu alanda <a href="https://guides.github.com/features/mastering-markdown/" target="_blank">Markdown</a> kullanabilirsiniz.</small>
                        <span class="helper-text"></span>
                    </div>
                </div>
                <div class="collection-item">
                    <div class="input-field">
                        <input name="password" id="password" type="password" class="validate" autocomplete="off" />
                        <label for="password">Şifre</label>
                        <small class="helper-text">Değiştirmek istemiyorsanız boş bırakın.</small>
                    </div>
                </div>
                <div class="collection-item d-flex">
                    <div class="input-field teal-text align-self-center" style="max-width: 240px;">
                        <input name="email" id="email" value="{{ $user->email }}" type="email" class="validate" autocomplete="off" />
                        <label for="email">E-posta</label>
                        <small class="helper-text">E-posta Adresi</small>
                    </div>
                    <label class="align-self-center ml-2">
                        <input name="verified" id="verified" value="on" type="checkbox" {{ $user->verified ? 'checked' : '' }} />
                        <span>Doğrulanmış</span>
                    </label>
                </div>
                <div class="collection-item">
                    <div class="input-field teal-text" style="max-width: 240px;">
                        <input name="gsm" id="gsm" value="{{ $user->gsm }}" type="text" class="validate" autocomplete="off" />
                        <label for="gsm">GSM</label>
                        <small class="helper-text">GSM Numarası</small>
                    </div>
                </div>
                <label class="collection-item waves-effect d-flex">
                    <span class="align-self-center" style="margin: 0 2rem 0 0;">
                        <input name="avatar" id="avatar" value="on" type="checkbox" />
                        <span>Avatarı Sil</span>
                    </span>
                    <img alt="Avatar" src="{{ $user->avatar() }}" class="user-avatar" style="width: 64px; height: 64px;" />
                </label>
                <label class="collection-item waves-effect d-block">
                    <input name="admin" id="admin" value="on" type="checkbox" {{ $user->admin() ? 'checked' : '' }} />
                    <span>Yönetici</span>
                </label>
                <label class="collection-item waves-effect d-block">
                    <input name="root" id="root" value="on" type="checkbox" {{ $user->root() ? 'checked' : '' }} />
                    <span>Sistem Sorumlusu</span>
                </label>
                <label class="collection-item waves-effect d-block">
                    <input name="moderator" id="moderator" value="on" type="checkbox" {{ $user->moderator() ? 'checked' : '' }} />
                    <span>Moderatör</span>
                </label>
                <div class="collection-item">
                    <div class="input-field">
                        <input name="ban_reason" id="ban_reason" value="{{ $user->ban_reason }}" type="text" class="validate" />
                        <label for="ban_reason">Ban Nedeni</label>
                        <small class="helper-text">Bu alanı doldurursanız kullanıcının sisteme erişimi engellenecektir.</small>
                    </div>
                </div>
            </div>
            <div class="card-action right-align">
                <button type="submit" class="btn-flat waves-effect">Güncelle</button>
            </div>
        </div>
    </form>
@endsection

@push('local.scripts')
    $(document).on('click', '[data-trigger=gsm-password]', function() {
        return modal({
            'id': 'alert',
            'body': 'Yeni bir şifre oluşturulacak ve giriş detayları kullanıcıya SMS ile bildirilecek. Onaylıyor musunuz?',
            'size': 'modal-small',
            'title': 'Şifre Gönder',
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect grey-text btn-flat',
                    'html': keywords.cancel
                }),
                $('<span />', {
                    'html': ' '
                }),
                $('<a />', {
                    'href': '#',
                    'class': 'waves-effect btn-flat teal-text json',
                    'html': keywords.ok,
                    'data-href': '{{ route('admin.user.password.gsm', $user->id) }}',
                    'data-method': 'post',
                    'data-callback': '__passwordByGSM'
                })
            ],
            'options': {}
        })
    })

    function __passwordByGSM(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ html: 'Yeni şifre sms ile gönderildi.', 'classes': 'green darken-2' })

            $('#modal-alert').modal('close')
        }
    }

    $(document).ready(function() {
        $('textarea#about').characterCounter()
    })
@endpush

@section('dock')
    @include('user.admin._menu', [ 'active' => 'account', 'id' => $user->id ])

    <div class="collection">
        <a href="#" class="collection-item waves-effect" data-trigger="gsm-password">
            SMS ile Yeni Şifre Gönder
        </a>
    </div>
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/jquery.maskedinput.min.js?v='.config('system.version')) }}"></script>
    <script>
        $('input#gsm').mask('(999) 999 99 99')
    </script>
@endpush
