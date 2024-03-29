/*!
 |-------------------------------
 | 8vz.net 1.0
 |-------------------------------
 | (c) 2019 - 8vz.net
 |-------------------------------
 */

$(function() {
    timeAgo()

    $('#loading').fadeOut()

    msie()
})

function flash_alert(_text, _class)
{
    $('body').append(
        $('<div />', {
            'id': 'flash-alert',
            'class': 'd-flex justify-content-center',
            'html': $('<div />', {
                'class': 'flash-alert-text align-self-center',
                'html': _text
            })
        }).addClass(_class)
    )

    setTimeout(function() {
        $('#flash-alert').animate({ 'opacity': 0 }, 600, function() {
            $(this).remove()
        })
    }, 1000)
}

function full_page_wrapper(html)
{
    $('body').addClass('fpw-active')

    $('#full_page-wrapper').children('.content').html(html)
}

function math_prices()
{
    var price = 0;

    $.each($('input[type=number][data-unit-price], input[type=range][data-unit-price]'), function(key, item) {
        var __ = $(this);
        var up = __.data('unit-price') * __.val();

        price = price + up;
    })

    return price;
}

function single_prices()
{
    var price = 0;

    $.each($('input[type=checkbox][data-unit-price]:checked'), function(key, item) {
        var __ = $(this);
        var up = __.data('unit-price');

        price = price + up;
    })

    return price;
}

function msie()
{
    var ua = window.navigator.userAgent;
    var msie = ua.indexOf('MSIE ');

    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))
    {
        M.toast({ html: 'Internet Explorer ile 8vz deneyiminiz sorunlu olacaktır. Lütfen 8vz için daha güncel bir tarayıcı kullanın. Bkz: Mozilla Firefox, Google Chrome', classes: 'red' })
    }
}

/* --- excel save --- */

$(document).on('click', '[data-excel]', function() {
    var __ = $(this);

    $(_element(__.data('excel'))).table2excel({
        exclude: '.noExl',
        filename: __.data('name'),
        fileext: '.xls'
    })
}).on('click', '[data-image]', function(e) {
    var __ = $(this);
    var target = $(__.data('image'));

    html2canvas(target, {
        onrendered: function(canvas) {
            return Canvas2Image.saveAsPNG(canvas);
        }
    })

    return e.preventDefault()
})

$(window).scroll(function() {
    var scrollTop = $(window).scrollTop();
    var nav = $('#main-nav');

    if (scrollTop >= 10)
    {
        if (!nav.hasClass('active'))
        {
            $('#main-nav').addClass('active')
        }
    }
    else
    {
        if (nav.hasClass('active'))
        {
            $('#main-nav').removeClass('active')
        }
    }
})

function autoWidth()
{
    $(this).attr('size', $(this).val().length);
}

/* --- hash --- */

function hashCode(s)
{
  return s.split('').reduce(function(a,b){a=((a<<5)-a)+b.charCodeAt(0);return a&a},0);              
}

/* --- number format --- */

function number_format(number)
{
    var thousand_separator = '.';

    var number_string = number.toString(),
        rest          = number_string.length % 3,
        result        = number_string.substr(0, rest),
        thousands     = number_string.substr(rest).match(/\d{3}/gi);

    if (thousands)
    {
        separator = rest ? thousand_separator : '';
        result += separator + thousands.join(thousand_separator);
    }

    return result;
}

/* --- price format --- */

function price_format(number)
{
    return parseFloat(Math.round(number * 100) / 100).toFixed(2);
}

/* --- str limit --- */

function str_limit(text, size)
{
    return text.length > size ? text.slice(0, size - 1) + '...' : text;
}

/* --- routing go function --- */

function __go(__, obj)
{
    if (obj.status == 'ok')
    {
        location.href = obj.route;
    }
}

/* --- human readable file sizes --- */

function humanFileSize(bytes, si)
{
    var thresh = si ? 1000 : 1024;

    if (Math.abs(bytes) < thresh)
    {
        return bytes + ' B';
    }

    var units = [ 'kB','MB','GB','TB','PB','EB','ZB','YB' ];

    var u = -1;

    do
    {
        bytes /= thresh;
        ++u;
    }
    while(Math.abs(bytes) >= thresh && u < units.length - 1);

    return bytes.toFixed(1)+' '+units[u];
}

/* --- slug function --- */

function slug(str)
{
    str = str.toLowerCase();

    var specialChars = [
        ['ğ', 'g'],
        ['ü', 'u'],
        ['ş', 's'],
        ['ı', 'i'],
        ['ö', 'o'],
        ['ç', 'c'],
        ['Ğ', 'g'],
        ['Ü', 'u'],
        ['Ş', 's'],
        ['İ', 'i'],
        ['Ö', 'o'],
        ['Ç', 'c'],
        ['-', ' ']
    ];

    for (var i = 0; i < specialChars.length; i++)
    {
        str = str.replace(eval('/' + specialChars[i][0] + '/ig'), specialChars[i][1]);
    }

    str = $.trim(str);

    return str.replace(/\s\s+/g, ' ').replace(/[^a-z0-9-\s]/gi, '').replace(/[^\w]/ig, '-');
}

/* --- scroll function --- */

function _scrollTo(scrollTo)
{
    var target = eval(_element(scrollTo.target));

    if (target.length)
    {
        var offset = target.offset();

        setTimeout(function() {
            $('html, body').animate({ scrollTop: (scrollTo.tolerance) ? offset.top + parseInt(scrollTo.tolerance): offset.top }, scrollTo.speed ? scrollTo.speed : 500);
        }, scrollTo.delay)
    }
}

/* --- goodbye function --- */

$(document).on('keydown keyup change click', '.goodbye', function() {
    window.onbeforeunload = goodbye; 
}).on('click', '[data-modal-alert]', function() {
    var __ = $(this);

    return modal({
        'id': 'alert',
        'body': $('<span />', {
            'html': __.data('modal-alert'),
            'css': {
                'font-size': '18px'
            }
        }),
        'title': __.data('modal-alert-title') ? __.data('modal-alert-title') : keywords.info,
        'size': __.data('modal-size') ? __.data('modal-size') : 'modal-small',
        'options': {},
        'footer': [
           $('<a />', {
               'href': '#',
               'class': 'modal-close waves-effect btn-flat',
               'html': keywords.ok
           })
        ]
    })
})

function goodbye(e)
{
    if (!e) e = window.event;
    if (e.stopPropagation) e.preventDefault()
}

/* --- time ago --- */

var time_ago_timer;

function timeAgo()
{
    window.clearTimeout(time_ago_timer)

    time_ago_timer = window.setTimeout(function() {
        $('time.timeago').timeago()
    }, 500)
}

/* --- disabled links --- */

$(document).on('click', 'a[href="#"], .disabled', function() {
    return false;
})

/* --- dock status --- */

$(document).on('click', '[data-dock]', function() {
    var __ = $(this);

    if (__.data('dock') == 'close')
    {
        $('body').removeClass('dock-active')
    }
    else if (__.data('dock') == 'open')
    {
        $('body').addClass('dock-active')
    }
})

/* --- remove function --- */

$(document).on('click', '[data-remove]', function() {
    var __ = $(this);

    var target = __.data('remove'),
        target = _element(target);

    target.remove()

    return false;
})

/* --- copy function --- */

$(document).on('click', '[data-clip]', function() {
    var __ = $(this);
        __.select()

    document.execCommand('copy')

    M.toast({ html: __.data('clip') })
})

/* --- focus function --- */

var focusDelay;

$(document).on('click', '[data-focus]', function() {
    var __ = $(this);

    var target = __.data('focus'),
        target = _element(target);

    window.clearTimeout(focusDelay)

    if (__.data('value'))
    {
        target.val(__.data('value'))
    }

    focusDelay = window.setTimeout(function() {
        target.focus()
    }, __.data('focus-delay'))

    return false;
})

/* --- focus function --- */

$(document).on('click', '[data-input]', function() {
    var __ = $(this);

    var target = __.data('input'),
        target = _element(target);

        target.val(__.data('input-value'))

    return false;
})

/* --- submit --- */

$(document).on('click', '[data-submit]', function() {
    var __ = $(this);

    var target = __.data('submit'),
        target = _element(target);

        target.submit()

    return false;
})

/* --- image to --- */

$(document).on('keydown keyup change click', '[data-image-to]', function() {
    var __ = $(this);

    var target = __.data('image-to'),
        target = _element(target);

    target.attr('src', __.val())
})

/* --- scroll to --- */

$(document).on('click', '[data-scroll-to]', function() {
    var __ = $(this);

    _scrollTo({
        'target': __.data('scroll-to'),
        'tolerance': __.data('scroll-to-tolerance')
    })
})

/* --- input to html --- */

$(document).on('keydown keyup change click', '[data-slug]', function() {
    var __ = $(this);

    var target = __.data('slug'),
        target = _element(target);

    target.val(slug(__.val()))

    M.updateTextFields()
})

/* --- submit function --- */

var submitDelay;

$(document).on('keyup', '[data-submit]', function(e) {
    var __ = $(this);

    if (e.which == __.data('key'))
    {
        var target = __.data('submit'),
            target = _element(target);

        window.clearTimeout(submitDelay)

        submitDelay = window.setTimeout(function() {
            target.submit()
        }, __.data('submit-delay'))
    }
})

/* --- class function --- */

var classDelay;

$(document).on('click', '[data-class]', function() {
    var __ = $(this);

    var target = __.data('class'),
        target = _element(target);

    window.clearTimeout(classDelay)

    classDelay = window.setTimeout(function() {
        var event;

        if (__.data('class-remove'))
        {
            target.removeClass(__.data('class-remove'))
            event = 'remove';
        }

        if (__.data('class-add'))
        {
            target.addClass(__.data('class-add'))
            event = 'add';
        }

        if (__.data('class-toggle'))
        {
            target.toggleClass(__.data('class-toggle'))
            event = 'toggle';
        }

        if (__.data('class-callback'))
        {
            eval(__.data('class-callback'))(event)
        }

        if (__.data('class-hide'))
        {
            var _target = __.data('class-hide'),
                _target = _element(_target);

                _target.addClass('hide')
        }
    }, __.data('class-delay'))

    return false;
})

/* --- checkbox value toggle function --- */

$('input[type=checkbox][data-checked-value]').change(function() {
    var __ = $(this);
        __.val(__.data(this.checked ? 'checked-value' : 'unchecked-value'))
})

/* --- focus class function --- */

var focusClassDelay;

$(document).on('focus', '[data-focus-class]', function() {
    var __ = $(this);

    var target = __.data('focus-class'),
        target = _element(target);

    window.clearTimeout(focusClassDelay)

    focusClassDelay = window.setTimeout(function() {
        if (__.data('focus-class-remove'))
        {
            target.removeClass(__.data('focus-class-remove'))
        }

        if (__.data('focus-class-add'))
        {
            target.addClass(__.data('focus-class-add'))
        }

        if (__.data('focus-class-toggle'))
        {
            target.toggleClass(__.data('focus-class-toggle'))
        }
    }, __.data('focus-class-delay'))

    return false;
})

/* --- blur class function --- */

var blurClassDelay;

$(document).on('blur', '[data-blur-class]', function() {
    var __ = $(this);

    var target = __.data('blur-class'),
        target = _element(target);

    window.clearTimeout(blurClassDelay)

    blurClassDelay = window.setTimeout(function() {
        if (__.data('blur-class-remove'))
        {
            target.removeClass(__.data('blur-class-remove'))
        }

        if (__.data('blur-class-add'))
        {
            target.addClass(__.data('blur-class-add'))
        }

        if (__.data('blur-class-toggle'))
        {
            target.toggleClass(__.data('blur-class-toggle'))
        }
    }, __.data('blur-class-delay'))

    return false;
})

/* --- selector function --- */

function _element(m) {
    var sp = m.split('->'),
        elem,
        selector = '';

    $.each(sp, function(key, val) {
        if (elem)
        {
            var brackets = val.split(/[(\)]/);

            selector = selector + '.' + brackets[0] + "('" + brackets[1] + "')";
        }
        else
        {
            elem = "$('" + val + "')";
        }
    })

    return eval(elem + selector);
}

/* --- popup --- */

$(document).on('click', '.popup', function() { 
    var __          = $(this),
        url         = __.data('url'),
        width       = __.data('width') ? __.data('width') : 600,
        height      = __.data('height') ? __.data('height') : 400,
        new_window  = window.open(url, 'name', 'height=' + height + ', width=' + width);

    if (window.focus)
    {
        new_window.focus()
    }

    return false;
})

/* --- cookies --- */

function setCookie(cookie_name, cookie_value, days)
{
    var d = new Date();
        d.setTime(d.getTime() + (days*24*60*60*1000));

    var expires = 'expires=' + d.toUTCString();

    document.cookie = cookie_name + '=' + cookie_value + ';' + expires + ';path=/';
}

function getCookie(cookie_name)
{
    var name = cookie_name + '=';
    var decoded_cookie = decodeURIComponent(document.cookie);
    var ca = decoded_cookie.split(';');

    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];

        while (c.charAt(0) == ' ')
        {
            c = c.substring(1);
        }

        if (c.indexOf(name) == 0)
        {
            return c.substring(name.length, c.length);
        }
    }
}

$(document).ready(function() {
    if (!getCookie('cookie-alert'))
    {
        $('.cookie-alert').removeClass('hide')
    }

    if (!getCookie('menu-alert'))
    {
        $('.menu-alert').removeClass('hide')
    }

    M.updateTextFields()

    $.extend({
        playSound: function () {
            return $('<audio />', {
                'class': 'sound-player hide',
                'autoplay': 'autoplay',
                'html': $('<source />', {
                    'src': arguments[0],
                    'html': $('<embed />', {
                        'src': arguments[0],
                        'hidden': 'true',
                        'autostart': 'true',
                        'loop': 'false'
                    })
                })
            }).appendTo('body')
        },
        stopSound: function () {
            $('.sound-player').remove();
        }
    });
}).on('click', '.cookie-alert', function() {
    $(this).addClass('hide')

    setCookie('cookie-alert', true, 7)

    return false;
}).on('click', '.dock-menu', function() {
    $('.menu-alert').addClass('hide')

    setCookie('menu-alert', true, 7)
})

/* --- json (ajax) --- */

var jsonTimer;

$(document).on('change', 'select.json, input[type=radio].json, input[type=checkbox].json', function(e) {
    var __ = $(this);

    window.clearTimeout(jsonTimer);

    jsonTimer = window.setTimeout(function() {
        var target = __.data('json-target') ? eval(_element(__.data('json-target'))) : __;

        if (__.data('clear'))
        {
            target.data('skip', 0).addClass('json-clear')
        }

        vzAjax(target)
    }, __.data('delay') ? __.data('delay') : 500)

    return false;
}).on('keyup', 'input.json, textarea.json', function(e) {
    var __ = $(this);

    window.clearTimeout(jsonTimer);

    jsonTimer = window.setTimeout(function() {
        var target = __.data('json-target') ? eval(_element(__.data('json-target'))) : __;

        if (__.data('clear'))
        {
            target.data('skip', 0).addClass('json-clear')
        }

        vzAjax(target)
    }, __.data('delay') ? __.data('delay') : 500)

    return false;
}).on('click', '.json:not(form):not(select):not(input):not(textarea)', function() {
    var __ = $(this);
    var target = __.data('json-target') ? eval(_element(__.data('json-target'))) : __;

    if (__.data('clear'))
    {
        target.data('skip', 0).addClass('json-clear')
    }

    vzAjax(target)

    return false;
}).on('submit', 'form.json', function() {
    var __ = $(this);
    var target = __.data('json-target') ? eval(_element(__.data('json-target'))) : __;

    if (__.data('clear'))
    {
        target.data('skip', 0).addClass('json-clear')
    }

    vzAjax(target)

    return false;
}).on('keydown', 'input.json-search', function() {
    var __ = $(this),
        target = eval(_element(__.data('json-target')));

        target.data('skip', 0).addClass('json-clear')
}).on('change', 'select.json-search', function() {
    var __ = $(this),
        target = eval(_element(__.data('json-target')));

        target.data('skip', 0).addClass('json-clear')
})

$(window).on('load', function() {
    $('.load').each(function() {
        var __ = $(this);

        setTimeout(function() {
            vzAjax(__)
        }, __.data('load-delay'))
    })
}).on('resize', function(e) {
    if ($(window).width() <= 992)
    {
        $('#slide-out').sidenav('close')
    }
})

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': token,
        'X-AJAX': true
    }
})

var error_timer;

function vzAjax(__)
{
    if (__.hasClass('disabled'))
    {
        return false;
    }

    if (__.hasClass('loading'))
    {
        $('#loading').fadeIn()
    }

    var method = 'GET',
        variables = {};

    if (__.is('form'))
    {
        $('#loading').fadeIn()

        var form = __.serializeArray(),
            items = '';

        __.find('button').attr('disabled', true)

        $.map(form, function(obj) {
            items = items == '' ? obj.name : items + ',' + obj.name
        })

        variables = $.extend(variables, getElementData(items.split(','), '#' + __.attr('id')))

        if (__.attr('method'))
        {
            method = __.attr('method').toUpperCase()
        }
    }
    else
    {
        if (!__.is('input'))
        {
            __.attr('disabled', true)
        }

        if (__.is('input') && __.attr('type') == 'checkbox')
        {
            $('input[name=' + __.attr('name') + ']').attr('disabled', true)
        }
    }

    if (__.is('input') || __.is('select') || __.is('textarea'))
    {
        var this_arr = {};

        this_arr[__.data('alias') ? __.data('alias') : __.attr('name')] = __.val();

        variables = $.extend(variables, this_arr)
    }

    variables = $.extend(variables, __.data())

    delete variables['alias'];
    delete variables['href'];

    if (__.data('method'))
    {
        method = __.data('method').toUpperCase()
    }

    if (__.data('include'))
    {
        variables = $.extend(variables, getElementData(__.data('include').split(','), null))

        delete variables['include'];
    }

    variables = $.extend(variables, { 'X-AJAX': true, 'token': token })

    var URL = __.is('form') ? __.attr('action') : __.data('href'),
        URL = (method == 'POST' || method == 'PATCH' || method == 'PUT' || method == 'DELETE') ? URL : URL + '/?' + $.param( variables, true );

    var _xhr = $.ajax({
        type: method,
        dataType: 'json',
        url: URL,
        crossDomain: URL.substring(0, 4) == 'http' ? true : false,
        data: (method == 'POST' || method == 'PATCH' || method == 'PUT' || method == 'DELETE') ? variables : '',
        beforeSend: function()
        {
            var callbefore = __.data('callbefore');

            if (callbefore)
            {
                eval(callbefore)(__);
            }
        },
        complete: function(obj)
        {
            //
        },
        error: function(jqXHR, exception, title, test)
        {
            __result(__)

            var err_callback = __.data('error-callback');

            if (err_callback)
            {
                eval(err_callback)(__);
            }
            else
            {
                if
                (
                    jqXHR.status == 0 ||
                    jqXHR.status == 403 ||
                    jqXHR.status == 404 ||
                    jqXHR.status == 405 ||
                    jqXHR.status == 429 ||
                    jqXHR.status == 'warn' ||
                    jqXHR.status == undefined
                )
                {
                    if (jqXHR.status == 0)
                    {
                        title = errors.disconnected;
                    }
                    else if (jqXHR.status == 429)
                    {
                        title = errors.calm;
                    }
                    else if (jqXHR.status == 404)
                    {
                        title = errors.notfound;
                    }
                    else if (jqXHR.status == 403)
                    {
                        title = errors.permission;
                    }
                    else if (jqXHR.status == 'warn')
                    {
                        title = jqXHR.reason;
                    }

                    window.clearTimeout(error_timer);

                    error_timer = setTimeout(function() {
                        return modal({
                            'id': 'err',
                            'body': title,
                            'size': 'modal-small',
                            'title': keywords.info,
                            'options': {},
                            'footer': [
                               $('<a />', {
                                   'href': '#',
                                   'class': 'modal-close waves-effect btn-flat',
                                   'html': keywords.ok
                               })
                            ]
                        })
                    }, jqXHR.status == 0 ? 2000 : 500)
                }
                else if (jqXHR.status == 401)
                {
                    location.href = jqXHR.responseJSON.redirect;
                }
                else if (jqXHR.status == 422)
                {
                    $.each(jqXHR.responseJSON.errors, function(key, text) {
                        key = key.replace(/([0-9.]+)/i, '');

                        var _element = __.is('form') ? __.find('[name=' + key + ']') :  $('[name=' + key + ']');
                        var feedback = _element.closest('.input-field').find('.helper-text');

                        if (feedback.length)
                        {
                            if (__.is('form'))
                            {
                                if ($('#' + __.attr('id')).closest('.model'))
                                {

                                }
                                else
                                {
                                    _scrollTo({
                                        'target': '#' + __.attr('id'),
                                        'tolerance': '-72px'
                                    })
                                }
                            }

                            _element.addClass('invalid')
                            feedback.attr('data-error', text.join(' '))
                        }
                        else
                        {
                            M.toast({ html: text[0], classes: 'red' })
                        }
                    })
                }
                else if (jqXHR.status == 500)
                {
                    if (debug)
                    {
                        var mdl = modal({
                            'id': 'err',
                            'body': [
                                $('<div />', {
                                    'class': 'error-area',
                                    'append': [
                                        $('<input />', {
                                            'type': 'text',
                                            'readonly': true,
                                            'value': method + ': ' + URL
                                        }),
                                        $('<input />', {
                                            'type': 'text',
                                            'readonly': true,
                                            'value': jqXHR.responseJSON.file
                                        }),
                                        $('<input />', {
                                            'type': 'text',
                                            'readonly': true,
                                            'value': 'Line: ' + jqXHR.responseJSON.line
                                        }),
                                        $('<pre />', {
                                            'html': jqXHR.responseJSON.message
                                        }),
                                        $('<ul />')
                                    ]
                                })
                            ],
                            'size': 'modal-large',
                            'title': title,
                            'options': {},
                            'footer': [
                                $('<a />', {
                                    'href': '#',
                                    'class': 'modal-close waves-effect btn-flat',
                                    'html': keywords.ok
                                })
                            ]
                        })

                        $.each(variables, function(key, val) {
                            $('<li />', {
                                'append': [
                                    $('<label />', {
                                        'html': key
                                    }),
                                    $('<input />', {
                                        'type': 'text',
                                        'readonly': true,
                                        'value': $('[name="' + key + '"]').attr('type') == 'password' ? '[secret]' : val
                                    })
                                ]
                            }).appendTo('.error-area > ul')
                        })
                    }
                    else
                    {
                        return modal({
                            'id': 'err',
                            'body': title,
                            'title': keywords.info,
                            'size': 'modal-small',
                            'options': {},
                            'footer': [
                                $('<a />', {
                                    'href': '#',
                                    'class': 'modal-close waves-effect btn-flat',
                                    'html': keywords.ok
                                })
                            ]
                        })
                    }
                }
                else if (jqXHR.status == 419)
                {
                    return modal({
                        'id': 'err',
                        'body': errors.time_out,
                        'title': keywords.info,
                        'size': 'modal-small',
                        'options': {},
                        'footer': [
                            $('<a />', {
                                'href': '#',
                                'class': 'modal-close waves-effect btn-flat',
                                'html': keywords.ok
                            })
                        ]
                    })
                }
                else
                {
                    console.error(jqXHR.status)
                }
            }
        },
        success: function(obj)
        {
            var callback = __.data('callback');

            if (obj.status == 'ok')
            {
                if (__.data('skip') !== undefined)
                {
                    if (__.hasClass('json-clear'))
                    {
                        __.children('._tmp').remove()
                        __.removeClass('json-clear')
                    }

                    if (obj.hits)
                    {
                        if (obj.hits.length)
                        {
                            __.data('skip', __.data('skip') + obj.hits.length)
                        }
                    }

                    var more_button = eval(_element(__.data('more-button')));

                    if (__.data('take') > obj.hits.length || obj.hits.length == 0)
                    {
                        more_button.addClass('hide')
                    }
                    else
                    {
                        more_button.removeClass('hide')
                    }
                }

                if (__.data('nothing') != undefined)
                {
                    if (!__.children('._tmp').length)
                    {
                        if (obj.hits.length)
                        {
                            __.children('.nothing').addClass('hide')
                        }
                        else
                        {
                            __.children('.nothing').removeClass('hide')
                        }
                    }
                }
            }
            else if (obj.status == 'err' && (obj.reason || obj.message))
            {
                modal({
                    'id': 'err',
                    'title': keywords.err,
                    'body': obj.reason ? obj.reason : obj.message,
                    'size': 'modal-small',
                    'options': {},
                    'footer': [
                        $('<a />', {
                            'href': '#',
                            'class': 'modal-close waves-effect btn-flat',
                            'html': keywords.ok
                        })
                    ]
                })
            }
            else if (obj.status == 'flash-alert')
            {
                flash_alert(obj.text, obj.class)
            }

            if (callback)
            {
                eval(callback)(__, obj);
            }

            if (obj.verification)
            {
                modal({
                    'id': 'alert',
                    'title': keywords.verification,
                    'body': verifications[obj.verification],
                    'size': 'modal-small',
                    'options': {},
                    'footer': [
                        $('<a />', {
                            'href': '#',
                            'class': 'modal-close waves-effect btn-flat',
                            'html': keywords.ok
                        })
                    ]
                })
            }

            __result(__)
            timeAgo()
        },
        timeout: __.data('timeout') ? __.data('timeout') : 60000
    })
}

function __result(__)
{
    if (__.data('loader') != undefined)
    {
        eval(_element(__.data('loader'))).addClass('hide')
    }

    setTimeout(function() {
        if (__.is('form'))
        {
            $('#loading').fadeOut()

            __.find('button').removeAttr('disabled')

            if (__.find('.captcha').length)
            {
                grecaptcha.reset(gr['gr-' + __.find('.recaptcha').data('id')])
            }
        }
        else
        {
            __.removeAttr('disabled')

            if (__.is('input') && __.attr('type') == 'checkbox')
            {
                $('input[name=' + __.attr('name') + ']').removeAttr('disabled')
            }
        }

        M.updateTextFields()

        if (__.hasClass('loading'))
        {
            $('#loading').fadeOut()
        }
    }, 100)
}

/* --- form elemanlarının değerlerini alır --- */

function getElementData(items, target) {
    var array = $('<div />');

    $.each(items, function(key, name) {
        var item = target ? eval(_element(target + '->find([name=' + name + '])')) : $('[name=' + name + ']');

        if (
            item.attr('type') &&
            (
                (
                    item.attr('type') == 'text' ||
                    item.attr('type') == 'datetime' ||
                    item.attr('type') == 'datetime-local' ||
                    item.attr('type') == 'email' ||
                    item.attr('type') == 'month' ||
                    item.attr('type') == 'date' ||
                    item.attr('type') == 'number' ||
                    item.attr('type') == 'range' ||
                    item.attr('type') == 'search' ||
                    item.attr('type') == 'tel' ||
                    item.attr('type') == 'time' ||
                    item.attr('type') == 'url' ||
                    item.attr('type') == 'week' ||
                    item.attr('type') == 'hidden' ||
                    item.attr('type') == 'password' ||
                    item.attr('type') == 'color'
                )
            ) ||
            (
                item.is('textarea')
            )
        )
        {
            var __ = target ? eval(_element(target + '->find(input[name="' + name + '"])')) : $('input[name="' + name + '"]'),
                arr = [];

            if (item.attr('multiple'))
            {
                for (i = 0; i < __.length; i++)
                {
                    arr[i] = __.eq(i).val();
                    array.data(item.data('alias') ? item.data('alias') : name, arr);
                }
            }
            else
            {
                array.data(item.data('alias') ? item.data('alias') : name, item.val());
            }
        }
        else if (item.is('select'))
        {
            var options = target ? eval(_element(target + '->find(select[name="' + name + '"])->children(option:checked)')) : $('select[name="' + name + '"] > option:checked'),
                arr = [];

            if (item.attr('multiple'))
            {
                for (i = 0; i < options.length; i++)
                {
                    arr[i] = options.eq(i).val();
                    array.data(item.data('alias') ? item.data('alias') : name, arr);
                }
            }
            else
            {
                array.data(item.data('alias') ? item.data('alias') : name, options.eq(0).val());
            }

        }
        else if (item.attr('type') == 'radio')
        {
            array.data(item.data('alias') ? item.data('alias') : name, target ? eval(_element(target + '->find(input[name="' + name + '"]:checked)')).val() : $('input[name="' + name + '"]:checked').val());
        }
        else if (item.attr('type') == 'checkbox')
        {
            var checkboxes = target ? eval(_element(target + '->find(input[name="' + name + '"]:checked)')) : $('input[name="' + name + '"]:checked'),
                arr = [];

            if (item.data('multiple') == true)
            {
                for (i = 0; i < checkboxes.length; i++)
                {
                    arr[i] = checkboxes.eq(i).val();
                    array.data(item.data('alias') ? item.data('alias') : name, arr);
                }
            }
            else
            {
                if (checkboxes.length == 1)
                {
                    array.data(item.data('alias') ? item.data('alias') : name, target ? eval(_element(target + '->find(input[name="' + name + '"]:checked)')).val() : $('input[name="' + name + '"]:checked').val());
                }
                else
                {
                    for (i = 0; i < checkboxes.length; i++)
                    {
                        arr[i] = checkboxes.eq(i).val();
                        array.data(item.data('alias') ? item.data('alias') : name, arr);
                    }
                }
            }
        }
        else if (item.attr('type') == 'file')
        {
            alert(0)
        }
    })

    return array.data();
}

/* --- modal --- */

function modal(obj)
{
    var z_index = 99999;

    var modal_id = '#modal-' + obj.id;
    var modal_size = obj.size ? obj.size : 'modal-large';
    var modal_class = obj.class ? obj.class : '';

    var modal_element = $('<div >', {
        'id': 'modal-' + obj.id,
        'class': 'modal with-bg ' + modal_size + ' ' + modal_class,
        'data-z-index': z_index,
        'css': { 'z-index': z_index },
        'html': [
            $('<div />', {
                'class': 'modal-content',
                'html': [
                    $('<div />', {
                        'class': 'modal-title'
                    }),
                    $('<div />', {
                        'class': 'modal-body',
                        'style': obj.minHeight ? 'min-height: ' + obj.minHeight : ''
                    })
                ]
            }),
            $('<div />', {
                'class': 'modal-footer'
            })
        ]
    });

    if (!$(modal_id).length)
    {
        modal_element.appendTo('body')
    }

    var modal = $(modal_id);

    /* --- modal --- */

    $('.modal').each(function() {
        var __ = $(this);

        if (__.data('z-index') >= z_index)
        {
            z_index = __.data('z-index') + 1;
        }
    })

    modal.data('z-index', z_index).css({ 'z-index': z_index });

    modal.modal(obj.options)

    modal.removeClass('modal-large modal-medium modal-small')
    modal.addClass(modal_size)

    modal.find('.modal-title').html(obj.title ? obj.title : '')
    modal.find('.modal-body').html(obj.body)
    modal.find('.modal-footer').html(obj.footer ? obj.footer : '')

    modal.modal('open')

    if (obj.timeout)
    {
        setTimeout(function() {
            $(modal_id).modal('close')
        }, obj.timeout)
    }

    return modal;
}

/* --- recaptcha function --- */

$(window).on('load', function() {
    captcha()
})

var gr = [];

function captcha()
{
    setTimeout(function()
    {
        $('.captcha').each(function() {
            var __ = $(this);

            if (__.hasClass('active'))
            {
                grecaptcha.reset(gr['gr-' + __.data('id')])
            }
            else
            {
                if (__.children('.g-recaptcha').length == 0)
                {
                    $('<div />', {
                        'class': 'g-recaptcha',
                        'id': __.data('id')
                    }).appendTo(__);
                }

                gr['gr-' + __.data('id')] = grecaptcha.render(__.data('id'), {
                    sitekey: recaptcha.site_key
                })

                __.addClass('active')
            }
        })
    }, 400)
}
