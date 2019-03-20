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
            'text' => '🐞 '.$organisation->name
        ]
    ],
    'dock' => true
])

@push('local.scripts')
    function __account(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({
                html: 'Organizasyon Bilgileri Güncellendi',
                classes: 'green darken-2'
            })
        }
    }
@endpush

@section('content')
    <form method="post" action="{{ route('admin.organisation', $organisation->id) }}" class="json" id="details-form" data-callback="__account">
        <div class="card with-bg">
            <div class="card-content">
                <span class="card-title">Organizasyon Bilgileri</span>
            </div>
            <div class="card-content">
                <ul class="item-group">
                    <li class="item">
                        <small class="grey-text">Oluşturuldu</small>
                        <p class="d-block">{{ date('d.m.Y H:i', strtotime($organisation->created_at)) }}</p>
                    </li>
                    <li class="item">
                        <small class="grey-text">Güncellendi</small>
                        <p class="d-block">{{ date('d.m.Y H:i', strtotime($organisation->created_at)) }}</p>
                    </li>
                </ul>
                <div class="collection">
                    <div class="collection-item">
                        <div class="input-field">
                            <input name="name" id="name" value="{{ $organisation->name }}" type="text" class="validate" />
                            <label for="name">Ad</label>
                            <small class="helper-text">Organizasyon adı.</small>
                        </div>
                    </div>
                    <div class="collection-item">
                        <div class="input-field" style="max-width: 128px;">
                            <select name="capacity" id="capacity">
                                @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" @if ($i == $organisation->capacity){{ 'selected' }}@endif>{{ $i }}</option>
                                @endfor
                            </select>
                            <label>Kapasite</label>
                            <small class="helper-text">Organizasyonun alabileceği maksimum kullanıcı sayısı.</small>
                        </div>
                    </div>
                    <div class="collection-item">
                        <small class="grey-text">Başlangıç Tarihi</small>
                        <p>{{ date('d.m.Y H:i', strtotime($organisation->start_date)) }}</p>
                    </div>
                    <div class="collection-item">
                        <div class="d-flex">
                            <div class="input-field" style="margin: 0 1rem 0 0;">
                                <input name="end_date" id="end_date" value="{{ date('Y-m-d', strtotime($organisation->end_date)) }}" type="text" class="validate datepicker" />
                                <label for="end_date">Bitiş Tarihi</label>
                                <small class="helper-text">Organizasyonun bitiş tarihi.</small>
                            </div>
                            <div class="input-field" style="margin: 0 1rem 0 0;">
                                <input name="end_time" id="end_time" value="{{ date('H:i', strtotime($organisation->end_date)) }}" type="text" class="validate timepicker" />
                                <label for="end_time">Bitiş Saati</label>
                                <small class="helper-text">Organizasyonun bitiş saati.</small>
                            </div>
                        </div>
                    </div>
                    <hr />
                    <div class="collection-item">
                        <div class="d-flex">
                            <div class="input-field" style="margin: 0 1rem 0 0;">
                                <input name="twitter_follow_limit_user" id="twitter_follow_limit_user" value="{{ $organisation->twitter_follow_limit_user }}" type="number" max="5000" class="validate" />
                                <label for="twitter_follow_limit_user">Twitter Kullanıcı Takip Limiti</label>
                                <small class="helper-text">Organizasyonun Twitter üzerinden takip edebileceği maksimum kullanıcısı sayısı.</small>
                            </div>
                            <div class="input-field" style="margin: 0 1rem 0 0;">
                                <input name="twitter_follow_limit_keyword" id="twitter_follow_limit_keyword" value="{{ $organisation->twitter_follow_limit_keyword }}" type="number" max="400" class="validate" />
                                <label for="twitter_follow_limit_keyword">Twitter Kelime Takip Limiti</label>
                                <small class="helper-text">Organizasyonun Twitter üzerinden takip edebileceği maksimum kelime sayısı.</small>
                            </div>
                        </div>
                    </div>
                    <div class="collection-item">
                        <div class="d-flex">
                            <div class="input-field" style="margin: 0 1rem 0 0;">
                                <input name="youtube_follow_limit_channel" id="youtube_follow_limit_channel" value="{{ $organisation->youtube_follow_limit_channel }}" type="number" max="100" class="validate" />
                                <label for="youtube_follow_limit_channel">YouTube Kanal Takip Limiti</label>
                                <small class="helper-text">Organizasyonun YouTube üzerinden takip edebileceği maksimum kanal sayısı.</small>
                            </div>
                            <div class="input-field" style="margin: 0 1rem 0 0;">
                                <input name="youtube_follow_limit_keyword" id="youtube_follow_limit_keyword" value="{{ $organisation->youtube_follow_limit_keyword }}" type="number" max="100" class="validate" />
                                <label for="youtube_follow_limit_keyword">YouTube Kelime Takip Limiti</label>
                                <small class="helper-text">Organizasyonun YouTube üzerinden takip edebileceği maksimum kelime sayısı.</small>
                            </div>
                            <div class="input-field" style="margin: 0 1rem 0 0;">
                                <input name="youtube_follow_limit_video" id="youtube_follow_limit_video" value="{{ $organisation->youtube_follow_limit_video }}" type="number" max="100" class="validate" />
                                <label for="youtube_follow_limit_video">YouTube Video Takip Limiti</label>
                                <small class="helper-text">Organizasyonun YouTube üzerinden takip edebileceği maksimum video sayısı.</small>
                            </div>
                        </div>
                    </div>
                    <hr />
                    <label class="collection-item waves-effect d-block">
                        <input name="status" id="status" value="on" type="checkbox" {{ $organisation->status ? 'checked' : '' }} />
                        <span>Aktif</span>
                    </label>
                </div>
            </div>
            <div class="card-action right-align">
                <button type="submit" class="btn-flat waves-effect">Güncelle</button>
            </div>
        </div>
    </form>
@endsection

@section('dock')
    @include('organisation.admin._menu', [ 'active' => 'organisation', 'id' => $organisation->id ])
@endsection

@push('local.scripts')
    $('select').formSelect()

    $('.datepicker').datepicker({
        firstDay: 0,
        format: 'yyyy-mm-dd',
        i18n: date.i18n
    })

    $('.timepicker').timepicker({
        format: 'hh:MM',
        twelveHour: false,
        i18n: date.i18n
    })
@endpush
