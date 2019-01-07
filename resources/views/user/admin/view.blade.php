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
            'text' => $user->name
        ]
    ],
    'dock' => true
])

@push('local.scripts')
    function __account(__, obj)
    {
        if (obj.status == 'ok')
        {
            $('input[name=password]').val('')
            $('input[name=avatar]').prop('checked', false)

            $('img.user-avatar').attr('src', obj.data.avatar ? '{{ url('/') }}/' + obj.data.avatar : '{{ asset('img/icons/people.png') }}')

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
        <div class="card">
            <div class="card-image">
                <img src="{{ asset('img/card-header.jpg') }}" alt="Hesap Bilgileri" />
                <span class="card-title">Hesap Bilgileri</span>
            </div>
            <div class="card-content">
                <ul class="item-group">
                    <li class="item">
                        <small class="grey-text">Oluşturuldu</small>
                        <p class="d-block">{{ date('d.m.Y H:i', strtotime($user->created_at)) }}</p>
                    </li>
                    <li class="item">
                        <small class="grey-text">Güncellendi</small>
                        <p class="d-block">{{ date('d.m.Y H:i', strtotime($user->created_at)) }}</p>
                    </li>
                </ul>
                <div class="collection">
                    <div class="collection-item">
                        <div class="input-field">
                            <input name="name" id="name" value="{{ $user->name }}" type="text" class="validate" />
                            <label for="name">Ad</label>
                            <small class="helper-text">Kullanıcının sistemdeki tam adı.</small>
                        </div>
                    </div>
                    <div class="collection-item">
                        <div class="input-field">
                            <input name="password" id="password" type="password" class="validate" />
                            <label for="password">Şifre</label>
                            <small class="helper-text">Değiştirmek istemiyorsanız boş bırakın.</small>
                        </div>
                    </div>
                    <div class="collection-item d-flex">
                        <div class="input-field teal-text align-self-center">
                            <input name="email" id="email" value="{{ $user->email }}" type="email" class="validate" />
                            <label for="email">E-posta</label>
                            <small class="helper-text">Kullanıcının sistemdeki e-posta adresi.</small>
                        </div>
                        <label class="align-self-center" style="padding: 0 0 0 1rem;">
                            <input name="verified" id="verified" value="on" type="checkbox" {{ $user->verified ? 'checked' : '' }} />
                            <span>Doğrulanmış</span>
                        </label>
                    </div>
                    <label class="collection-item waves-effect d-flex">
                        <span class="align-self-center" style="margin: 0 2rem 0 0;">
                            <input name="avatar" id="avatar" value="on" type="checkbox" />
                            <span>Avatarı Sil</span>
                        </span>
                        <img alt="Avatar" src="{{ $user->avatar() }}" class="user-avatar" style="width: 64px; height: 64px;" />
                    </label>
                    <label class="collection-item waves-effect d-block">
                        <input name="root" id="root" value="on" type="checkbox" {{ $user->root() ? 'checked' : '' }} />
                        <span>Yönetici</span>
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
            </div>
            <div class="card-action right-align">
                <button type="submit" class="btn-flat waves-effect">Güncelle</button>
            </div>
        </div>
    </form>
@endsection

@section('dock')
    @include('user.admin._menu', [ 'active' => 'account', 'id' => $user->id ])
@endsection
