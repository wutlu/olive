@extends('layouts.app', [
    'sidenav_fixed_layout' => true,
    'breadcrumb' => [
        [
            'text' => 'Admin'
        ],
        [
            'text' => 'Sistem İzleme'
        ],
        [
            'text' => '🐞 Log Ekranı'
        ]
    ]
])

@push('local.scripts')
    var logTimer;

    function __log(__, obj)
    {
        if (obj.status == 'ok')
        {
            var collection = $('ul#console');
            var model = collection.children('li.collection-item.hide');

            if (obj.data.length)
            {
                var scroll = false;

                $.each(obj.data, function(key, o) {
                    var m = $('[data-id=' + o.uuid + ']');

                    var item = m.length ? m : model.clone();
                        item.removeClass('hide')
                            .attr('data-id', o.uuid)

                        item.find('[data-name=level]').html(o.level + '. seviye').addClass(o.level <= 4 ? 'green-text' : o.level <= 7 ? 'orange-text' : 'red-text')
                        item.find('[data-name=repeat]').html(o.hit + ' tekrar').addClass(o.hit <= 10 ? 'green-text' : o.hit <= 20 ? 'orange-text' : 'red-text')
                        item.find('[data-name=updated-at]').attr('data-time', o.updated_at)
                        item.find('[data-name=created-at]').attr('data-time', o.created_at)
                        item.find('[data-name=module]').html(o.module)
                        item.find('[data-name=message]').html(o.message)

                    if (m.length)
                    {
                        if (m.attr('data-repeat') != o.hit)
                        {
                            item.attr('data-repeat', o.hit)
                        }
                    }
                    else
                    {
                        item.find('[data-name=updated-at]').html(o.updated_at)
                        item.find('[data-name=created-at]').html(o.created_at)
                        item.attr('data-repeat', o.hit)
                    }

                    item.appendTo(collection)
                })
            }

            window.clearTimeout(logTimer)

            logTimer = window.setTimeout(function() {
                vzAjax($('ul#console'))
            }, 10000)

            var files = $('#files');
            var file_model = files.children('.collection-item.hide');

            if (obj.files.length)
            {
                $.each(obj.files, function(key, o) {
                    var m = $('[data-file-id=' + o.id + ']');

                    var file_item = m.length ? m : file_model.clone();
                        file_item.removeClass('hide').attr('data-file-id', o.id)

                        file_item.find('[data-name=path]').html(o.path)
                        file_item.find('[data-name=size]').html(o.size.readable)

                        file_item.prependTo(files)
                })
            }
        }
    }

    function __clear(__, obj)
    {
        if (obj.status == 'ok')
        {
            M.toast({ 
                html: 'Tüm geçici/kullanılmayan kayıtlar silindi.',
                classes: 'green darken-2'
            })

            $('#modal-clear').modal('close')
        }
    }

    $(document).on('click', '[data-trigger=clear]', function() {
        var mdl = modal({
                'id': 'clear',
                'body': 'Log vb. tüm geçici/kullanılmayan kayıtları silmek istediğinizden emin misiniz?',
                'size': 'modal-small',
                'title': 'Temizle',
                'options': {},
                'footer': [
                    $('<a />', {
                        'href': '#',
                        'class': 'modal-close waves-effect btn-flat grey-text',
                        'html': buttons.cancel
                    }),
                    $('<span />', {
                        'html': ' '
                    }),
                    $('<a />', {
                        'href': '#',
                        'class': 'waves-effect btn-flat json',
                        'html': buttons.ok,
                        'data-href': '{{ route('admin.monitoring.log.clear') }}',
                        'data-method': 'delete',
                        'data-callback': '__clear'
                    })
                ]
            });
    })
@endpush

@section('action-bar')
    <a href="#" class="btn-floating btn-large halfway-fab waves-effect white" data-trigger="clear">
        <i class="material-icons grey-text text-darken-2">clear_all</i>
    </a>
@endsection

@section('content')
    <div class="card with-bg">
        <div class="card-content">
            <span class="card-title">Log Dosyaları</span>
        </div>
        <ul id="files" class="collection">
            <li class="collection-item hide" data-href="{{ route('admin.monitoring.log.clear') }}">
                <span data-name="path"></span>
                <span data-name="size" class="badge grey darken-4 white-text"></span>
            </li>
        </ul>
        <ul
            id="console"
            class="collection load d-flex align-items-end flex-wrap no-select"
            data-href="{{ route('admin.monitoring.log') }}"
            data-callback="__log"
            data-method="post">
            <li class="collection-item hide" style="width: 100%;">
                <p>
                    <span data-name="level"></span>
                    <span class="grey-text text-lighten-2" style="padding: 0 .2rem;">/</span>
                    <span data-name="repeat"></span>
                    <span class="grey-text text-lighten-2" style="padding: 0 .2rem;">/</span>
                    <time data-name="updated-at" class="timeago grey-text text-darken-2"></time>
                </p>
                <p>
                    <time data-name="created-at" class="timeago grey-text text-darken-2"></time>
                    <span data-name="module" class="grey-text text-darken-2"></span>
                </p>
                <textarea data-name="message" class="green-text d-block"></textarea>
            </li>
        </ul>
    </div>
@endsection
