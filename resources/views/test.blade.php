@extends('layouts.app', [
    'footer_hide' => true
])

@push('local.styles')
    .query {
        padding: 2rem 1rem 1rem 1rem;
        margin: 1px;

        border-width: 1px;
        border-style: dashed;
        border-color: inline;

        border-radius: .4rem;

        position: relative;

        display: -ms-flexbox;
        display: flex;

        -ms-flex-wrap: wrap;
            flex-wrap: wrap;

        -webkit-transition: all 200ms cubic-bezier(.25, .46, .45, .94);
                transition: all 200ms cubic-bezier(.25, .46, .45, .94);
    }

    .query:before {
        content: attr(data-title);
        color: inline;
        font-size: 10px;

        background-color: #fff;
        border-radius: .4rem 0 .4rem 0;

        margin: -2rem .1rem 1rem -1rem;
        padding: .2rem 1rem;
        display: table;

        position: absolute;
    }

    .query.cloned {
        cursor: pointer;
        min-width: 240px;

        background-repeat: no-repeat;
        background-position: right .4rem top .4rem;
        background-size: 16px 16px;
    }
    .query.cloned:hover {
        background-image: url(../img/icons/delete.svg);
    }

    .query.bucket-ok:before {
        display: none;
    }
    .query.bucket-ok {
        display: table;

        padding: .4rem 1rem;

        min-width: auto;

        border-width: 0;

        background-color: #ef5350;
        background-position: right .4rem center;

        color: #fff !important;

        -ms-flex-item-align: center;
        align-self: center;
    }
    .query.bucket-ok:hover {
        padding-right: 32px;
    }

    ul.query-bucket {
    }
    ul.query-bucket > li.item > span.draggable {
        display: table;

        border-width: 1px;
        border-style: dashed;
        border-color: inline;
        border-radius: .4rem;

        cursor: move;

        text-transform: uppercase;
        font-size: 14px;

        margin: .4rem;
        padding: .4rem 1rem;
    }

    ul.bucket-column {
        width: 240px;
    }
    ul.bucket-column > li > .collapsible-body {
        padding: 0 1rem;
    }
@endpush

@push('local.scripts')
    $(document).on('click', '.query', function(e) {
        e.stopPropagation()

        var __ = $(this);

        if (__.hasClass('cloned'))
        {
            __.remove()
        }
    })

    $('.query-bucket .draggable').draggable({
        containment: 'document',
        helper: 'clone',
        cursor: 'move',
        revert: true
    })

    builder($('.query'))

    function builder(selector)
    {
        selector.droppable(
            {
                greedy: true,
                classes: {
                    'ui-droppable-active': 'da-active',
                    'ui-droppable-hover': 'da-hover'
                },
                drop: function(event, ui)
                {
                    var __ = $(this);

                    // sürüklenen: ui.helper.data('type')
                    // kapsayan: __.data('parent')

                    if (__.data('childs'))
                    {
                        if ($.inArray(ui.helper.data('type'), __.data('childs').split('|')) !== -1)
                        {
                            //
                        }
                        else
                        {
                            M.toast({
                                html: 'Değer, bu alana ait değil!',
                                classes: 'red darken-2'
                            }, 200)

                            return false;
                        }
                    }

                    if (ui.helper.data('parent'))
                    {
                        if (!__.closest('[data-type=' + ui.helper.data('parent') + ']').length)
                        {
                            M.toast({
                                html: 'Değer, bu alana ait değil!',
                                classes: 'red darken-2'
                            }, 200)

                            return false;
                        }
                    }

                    if (ui.helper.data('type') == __.data('type'))
                    {
                        M.toast({
                            html: 'Değer, kendi değeri ile iç içe olmamalıdır!',
                            classes: 'red darken-2'
                        }, 200)

                        return false;
                    }

                    var query = $('<div />', {
                        'class': 'query cloned',
                        'data-title': ui.helper.html(),
                        'data-type': ui.helper.data('type'),
                        'css': {
                            'color': ui.helper.css('color')
                        }
                    });

                    if (ui.helper.data('childs'))
                    {
                        query.attr('data-childs', ui.helper.data('childs'))
                    }

                    if (ui.helper.data('main'))
                    {
                        var failed = false;

                        $.each(__.closest('[data-main=true]'), function (k, v) {
                            if (v.dataset.type != ui.helper.data('parent'))
                            {
                                failed = true;
                            }
                        })

                        if (failed)
                        {
                            M.toast({
                                html: '2 ana grup iç içe olmamalı!',
                                classes: 'red darken-2'
                            }, 200)

                            return false;
                        }

                        query.attr('data-main', true)
                    }

                    var method = ui.helper.data('method');

                    if (method)
                    {
                        query.attr('data-method', method)
                    }

                    if (method == 'ok')
                    {
                        query.html(ui.helper.data('type')).addClass('bucket-ok', true)
                    }
                    else
                    {
                        if (method)
                        {
                            var promt_message = '"' + ui.helper.html() + '" girin:';

                            if (method == 'number')
                            {
                                promt_message = promt_message + ' "< küçüktür" veya "> büyüktür" kullanabilirsiniz. ';

                                var prompt_value = prompt(promt_message);

                                var _int = prompt_value.replace('<', '').replace('>', '');

                                if (Number.isInteger(+_int))
                                {
                                    query.html(ui.helper.data('type') + ':' + prompt_value);
                                }
                                else
                                {
                                    M.toast({
                                        html: 'Değer, nümerik olmalıdır!',
                                        classes: 'red darken-2'
                                    }, 200)

                                    return false;
                                }
                            }
                            else if (method == 'string')
                            {
                                var prompt_value = prompt(promt_message);

                                if (ui.helper.data('type') == 'word')
                                {
                                    query.html(prompt_value);
                                }
                                else if (ui.helper.data('type') == 'sentence')
                                {
                                    query.html('"' + prompt_value + '"');
                                }
                                else
                                {
                                    query.html(ui.helper.data('type') + ':' + prompt_value);
                                }
                            }

                            if (!prompt_value)
                            {
                                M.toast({
                                    html: 'Değer boş olamaz!',
                                    classes: 'red darken-2'
                                }, 200)

                                return false;
                            }
                        }
                    }

                    query.appendTo(__)

                    if (ui.helper.data('dynamic') == true)
                    {
                        builder(query)
                    }
                }
            }
        )
    }

    $(document).on('click', '.build', function() {
        var ii = _eacher($('.query[data-type=main]'));

        var eacher = _eacher($('.query[data-type]'));
        var stringer = _stringer(eacher, '');

        console.log(stringer)
    })

    function _stringer(array, string)
    {
        var seperator = ' || ';

        $.each(array, function(k, o) {
            var new_string = '';

            switch (o.key)
            {
                case 'or':
                case 'media':
                case 'sozluk':
                case 'twitter':
                case 'youtube':
                    seperator = ' || ';
                break;
                case 'and':
                    seperator = ' && ';
                break;
                case '+':
                    new_string = ' +';
                break;
                case '-':
                    new_string = ' -';
                break;
                case 'word':
                case 'sentence':
                    new_string = o.string + '1';
                break;
                default:
                    new_string = o.key + '2';
                break;
            }
            if (o.items)
            {

            }

            if (new_string)
            {
                if (string)
                {
                    string = string + seperator + new_string;
                }
                else
                {
                    string = new_string;
                }
            }
        })

        return string;
    }

    function _eacher(element)
    {
        var data = [];
        var elements = element.children('[data-type]');

        $.each(elements, function(k, o) {
            var __ = $(this);
            var item = {
                'key': __.data('type')
            };

            if (__.children('[data-type]').length)
            {
                item.items = _eacher(__)
            }

            if (__.data('method') == 'string' || __.data('method') == 'number')
            {
                item.string = __.html();
            }

            data.push(item);
        })

        return data;
    }
@endpush

@section('content')
    <div class="d-flex p-1">
        <ul class="collapsible bucket-column">
            <li class="active">
                <div class="collapsible-header">Temel</div>
                <div class="collapsible-body">
                    <ul class="query-bucket">
                        <li class="item">
                            <span class="draggable deep-purple-text" data-type="and" data-dynamic="true">Grup ( VE )</span>
                        </li>
                        <li class="item">
                            <span class="draggable blue-text" data-type="or" data-dynamic="true">Grup ( VEYA )</span>
                        </li>
                        <li class="item">
                            <span class="draggable green-text" data-type="+" data-dynamic="true">Olsun</span>
                        </li>
                        <li class="item">
                            <span class="draggable red-text" data-type="-" data-dynamic="true">Olmasın</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="word" data-method="string">Kelime</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="sentence" data-method="string">Cümle</span>
                        </li>
                    </ul>
                </div>
            </li>
            <li>
                <div class="collapsible-header">Medya</div>
                <div class="collapsible-body">
                    <ul class="query-bucket">
                        <li class="item">
                            <span class="draggable" data-type="media" data-dynamic="true" data-main="true">Medya Grubu ( VEYA )</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="site.id" data-parent="media" data-method="number">Site Id</span>
                        </li>
                    </ul>
                </div>
            </li>
            <li>
                <div class="collapsible-header">Sözlük</div>
                <div class="collapsible-body">
                    <ul class="query-bucket">
                        <li class="item">
                            <span class="draggable" data-type="sozluk" data-dynamic="true" data-main="true">Sözlük Grubu ( VEYA )</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="author" data-parent="sozluk" data-method="string">Yazar Adı</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="id" data-parent="sozluk" data-method="number">Entry Id</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="title" data-parent="sozluk" data-method="string">Konu Başlığı</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="site.id" data-parent="sozluk" data-method="number">Sözlük Id</span>
                        </li>
                    </ul>
                </div>
            </li>
            <li>
                <div class="collapsible-header">Twitter</div>
                <div class="collapsible-body">
                    <ul class="query-bucket">
                        <li class="item">
                            <span class="draggable" data-type="twitter" data-dynamic="true" data-main="true">Twitter Grubu ( VEYA )</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="user.screen_name" data-parent="twitter" data-method="string">Kullanıcı Adı</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="user.id" data-parent="twitter" data-method="number">Kullanıcı Id</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="user.verified:true" data-parent="twitter" data-method="ok">Doğrulanmış Hesaplar</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="external.type:quote" data-parent="twitter" data-method="ok">Sadece Alıntılar</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="external.type:reply" data-parent="twitter" data-method="ok">Sadece Cevaplar</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="counts.hashtag" data-parent="twitter" data-method="number">Hashtag Sayısı</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="counts.mention" data-parent="twitter" data-method="number">Mention Sayısı</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="counts.url" data-parent="twitter" data-method="number">Bağlantı Sayısı</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="counts.media" data-parent="twitter" data-method="number">Medya Sayısı</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="user.counts.statuses" data-parent="twitter" data-method="number">Kullanıcı Tweet Sayısı</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="user.counts.favourites" data-parent="twitter" data-method="number">Kullanıcı Favori Sayısı</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="user.counts.listed" data-parent="twitter" data-method="number">Kullanıcı Liste Sayısı</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="user.counts.friends" data-parent="twitter" data-method="number">Kullanıcı Takip Sayısı</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="user.counts.followers" data-parent="twitter" data-method="number">Kullanıcı Takipçi Sayısı</span>
                        </li>
                    </ul>
                </div>
            </li>
            <li>
                <div class="collapsible-header">YouTube</div>
                <div class="collapsible-body">
                    <ul class="query-bucket">
                        <li class="item">
                            <span class="draggable" data-type="youtube" data-dynamic="true" data-main="true">YouTube Grubu ( VEYA )</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="channel.title" data-parent="youtube" data-method="string">Kanal Başlığı</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="channel.id" data-parent="youtube" data-method="number">Kanal Id</span>
                        </li>
                        <li class="item">
                            <span class="draggable" data-type="video_id" data-parent="youtube" data-method="number">Video Id</span>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>

        <div>
            <div class="query" data-title="Sorgu Alanı ( VEYA )" data-type="main"></div>

            <span class="grey-text d-table mt-1">
                @component('components.alert')
                    @slot('icon', 'info')
                    @slot('text', 'Sorgu oluşturmak için, parametreleri sürükle bırak yöntemi ile kullanabilirsiniz.')
                @endcomponent
            </span>
        </div>
    </div>

    <button type="button" class="btn-flat waves-effect build">Derle</button>
@endsection

@push('external.include.footer')
    <script src="{{ asset('js/jquery.ui.min.js?v='.config('system.version')) }}"></script>
@endpush
