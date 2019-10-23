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
            'text' => 'Kayıtlı Aramalar',
            'link' => route('admin.organisation.saved_searches', $organisation->id)
        ],
        [
            'text' => '🐞 '.($search ? $search->name : 'Arama Oluştur')
        ]
    ],
    'dock' => true,
    'footer_hide' => true
])

@section('content')
    <form
        method="post"
        class="json"
        action="{{
            $search ?
                route('admin.organisation.saved_search', [ 'id' => $organisation->id, 'search_id' => $search->id ])
                :
                route('admin.organisation.saved_search', [ 'id' => $organisation->id ])
            }}"
        id="form"
        data-callback="__save">
        <div class="card">
            <div class="collection collection-unstyled">
                <div class="collection-item">
                    <div class="d-flex justify-content-between flex-wrap">
                        <div class="align-self-center">
                            <div class="input-field">
                                <input placeholder="Arama Adı" name="name" id="name" type="text" class="validate" value="{{ @$search->name }}" />
                            </div>
                        </div>
                        <label class="align-self-center">
                            <input name="reverse" value="on" type="checkbox" {{ @$search->reverse ? 'checked' : '' }} />
                            <span>İlk İçerikler</span>
                        </label>
                    </div>
                </div>
                <div class="collection-item">
                    <div class="input-field">
                        <input name="string" id="string" type="text" class="validate" value="{{ @$search->string }}" />
                        <label for="string">Sorgu Satırı</label>
                    </div>
                </div>
                <div class="collection-item">
                    <div class="d-flex">
                        <div class="d-flex flex-column flex-fill">
                            <h6 class="blue-grey-text">Duygu</h6>
                            <div class="switch">
                                <label>
                                    <input type="checkbox" name="sentiment_pos" value="5" {{ @$search->sentiment_pos ? 'checked' : '' }} />
                                    <span class="lever"></span>
                                    Pozitif
                                </label>
                            </div>
                            <div class="switch">
                                <label>
                                    <input type="checkbox" name="sentiment_neu" value="5" {{ @$search->sentiment_neu ? 'checked' : '' }} />
                                    <span class="lever"></span>
                                    Nötr
                                </label>
                            </div>
                            <div class="switch">
                                <label>
                                    <input type="checkbox" name="sentiment_neg" value="5" {{ @$search->sentiment_neg ? 'checked' : '' }} />
                                    <span class="lever"></span>
                                    Negatif
                                </label>
                            </div>
                            <div class="switch">
                                <label>
                                    <input type="checkbox" name="sentiment_hte" value="5" {{ @$search->sentiment_hte ? 'checked' : '' }} />
                                    <span class="lever"></span>
                                    Nefret Söylemi
                                </label>
                            </div>
                        </div>
                        <div class="d-flex flex-column flex-fill">
                            <h6 class="blue-grey-text">Müşteri</h6>
                            <div class="switch">
                                <label>
                                    <input type="checkbox" name="consumer_que" value="5" {{ @$search->consumer_que ? 'checked' : '' }} />
                                    <span class="lever"></span>
                                    Soru
                                </label>
                            </div>
                            <div class="switch">
                                <label>
                                    <input type="checkbox" name="consumer_req" value="5" {{ @$search->consumer_req ? 'checked' : '' }} />
                                    <span class="lever"></span>
                                    İstek
                                </label>
                            </div>
                            <div class="switch">
                                <label>
                                    <input type="checkbox" name="consumer_cmp" value="5" {{ @$search->consumer_cmp ? 'checked' : '' }} />
                                    <span class="lever"></span>
                                    Şikayet
                                </label>
                            </div>
                            <div class="switch">
                                <label>
                                    <input type="checkbox" name="consumer_nws" value="5" {{ @$search->consumer_nws ? 'checked' : '' }} />
                                    <span class="lever"></span>
                                    Haber
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr />

            <div class="d-flex flex-wrap">
                <div class="collection collection-unstyled flex-fill">
                    <div class="collection-item">
                        <h6 class="blue-grey-text">Cinsiyet</h6>
                        <div class="d-flex flex-column">
                            <label class="d-block">
                                <input name="gender" type="radio" value="all" {{ @$search->gender ? ($search->gender == 'all' ? 'checked' : '') : 'checked' }} />
                                <span>Hepsi</span>
                            </label>
                            <label class="d-block">
                                <input name="gender" type="radio" value="female" {{ @$search->gender == 'female' ? 'checked' : '' }} />
                                <span>Kadın</span>
                            </label>
                            <label class="d-block">
                                <input name="gender" type="radio" value="male" {{ @$search->gender == 'male' ? 'checked' : '' }} />
                                <span>Erkek</span>
                            </label>
                            <label class="d-block">
                                <input name="gender" type="radio" value="unknown" {{ @$search->gender == 'unknown' ? 'checked' : '' }} />
                                <span>Bilinmeyen</span>
                            </label>
                        </div>
                    </div>
                    <div class="collection-item">
                        <h6 class="blue-grey-text">Kategori</h6>
                        <div class="flex flex-column">
                            <label class="d-block">
                                <input type="radio" name="category" id="category" value="" checked />
                                <span>Tümü</span>
                            </label>
                            @foreach(config('system.analysis.category.types') as $key => $cat)
                                <label class="d-block">
                                    <input type="radio" name="category" id="category" value="{{ $key }}" {{ @$search->category == $key ? 'checked' : '' }} />
                                    <span>{{ $cat['title'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div>
                    <ul class="collection collection-unstyled">
                        <li class="collection-item">
                            <h6 class="blue-grey-text">Kaynaklar</h6> 
                        </li>
                        @foreach (config('system.modules') as $key => $module)
                            <li class="collection-item">
                                @if ($key == 'twitter')
                                    <div class="d-flex justify-content-between mb-2">
                                        <label class="module-label">
                                            <input name="modules" value="{{ $key }}" data-multiple="true" type="checkbox" {{ @in_array($key, $search->modules) ? 'checked' : '' }} />
                                            <span>{{ $module }}</span>
                                        </label>

                                        <label class="module-label" data-tooltip="Olive, Twitter iyi sonuç algoritması." data-position="left">
                                            <input name="sharp" value="on" type="checkbox" {{ @$search->sharp ? 'checked' : '' }} />
                                            <span>İyi Sonuç</span>
                                        </label>
                                    </div>
                                    <div class="d-flex">
                                        <div class="input-field">
                                            <select name="twitter_sort" id="twitter_sort">
                                                <option value="">Normal</option>
                                                    <option value="counts.favorite" {{ @$search->twitter_sort == 'counts.favorite' ? 'selected' : '' }}>Favori</option>
                                                    <option value="counts.retweet" {{ @$search->twitter_sort == 'counts.retweet' ? 'selected' : '' }}>ReTweet</option>
                                                    <option value="counts.quote" {{ @$search->twitter_sort == 'counts.quote' ? 'selected' : '' }}>Alıntı</option>
                                                    <option value="counts.reply" {{ @$search->twitter_sort == 'counts.reply' ? 'selected' : '' }}>Cevap</option>
                                                    <option value="" disabled>---</option>
                                                    <option value="user.counts.followers" {{ @$search->twitter_sort == 'user.counts.followers' ? 'selected' : '' }}>Takipçi</option>
                                                    <option value="user.counts.friends" {{ @$search->twitter_sort == 'user.counts.friends' ? 'selected' : '' }}>Takip</option>
                                                    <option value="user.counts.statuses" {{ @$search->twitter_sort == 'user.counts.statuses' ? 'selected' : '' }}>Tweet</option>
                                            </select>
                                            <label>Twitter Sıralaması</label>
                                        </div>
                                        <div class="input-field">
                                            <select name="twitter_sort_operator" id="twitter_sort_operator">
                                                <option value="desc" {{ @$search->twitter_sort_operator == 'desc' ? 'selected' : '' }}>Azalan</option>
                                                <option value="asc" {{ @$search->twitter_sort_operator == 'asc' ? 'selected' : '' }}>Artan</option>
                                            </select>
                                        </div>
                                    </div>
                                @elseif ($key == 'news')
                                    <div class="d-flex justify-content-between mb-2">
                                        <label class="module-label">
                                            <input name="modules" value="{{ $key }}" data-multiple="true" type="checkbox" {{ @in_array($key, $search->modules) ? 'checked' : '' }} />
                                            <span>{{ $module }}</span>
                                        </label>
                                    </div>
                                    <div class="input-field">
                                        <select name="state" id="state">
                                            <option value="">Hepsi</option>
                                            @foreach ($states as $state)
                                                <option value="{{ $state->name }}" {{ $state->name == @$search->state ? 'selected' : '' }}>{{ $state->name }}</option>
                                            @endforeach
                                        </select>
                                        <label>Yerel Basın</label>
                                    </div>
                                @else
                                    <label class="module-label">
                                        <input name="modules" value="{{ $key }}" data-multiple="true" type="checkbox" {{ @in_array($key, $search->modules) ? 'checked' : '' }} />
                                        <span>{{ $module }}</span>
                                    </label>
                                @endif
                            </li>
                        @endforeach
                        <li class="collection-item">
                            <div class="input-field">
                                <select name="take" id="take">
                                    <option value="10" {{ @$search->take == 10 ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ @$search->take == 20 ? 'selected' : '' }}>20</option>
                                    <option value="40" {{ @$search->take == 40 ? 'selected' : '' }}>40</option>
                                </select>
                                <label>Sayfalama</label>
                                <span class="helper-text">Her kaynak için gösterilecek içerik sayısı.</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-action right-align">
                <button type="submit" class="btn-flat waves-effect">{{ @$search ? 'Güncelle' : 'Oluştur' }}</button>
            </div>
        </div>
    </form>
@endsection

@section('dock')
    @include('organisation.admin._menu', [ 'active' => 'saved.searches', 'id' => $organisation->id ])
@endsection

@push('local.scripts')
    $('select').formSelect()

    function __save(__, obj)
    {
        if (obj.status == 'ok')
        {
            if (obj.data.status == 'created')
            {
                location.href = '{{ route('admin.organisation.saved_searches', $organisation->id) }}';
            }
            else
            {
                M.toast({ 'html': 'Kayıtlı arama güncellendi!', 'classes': 'green darken-2' })
            }
        }
    }
@endpush
