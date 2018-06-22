/* --- scroll function --- */

function scrollTo(scrollTo)
{
    var target = eval(element(scrollTo.target));

    if (target.length) {
        var offset = target.offset();

        setTimeout(function() {
            $('html, body').animate({ scrollTop: (scrollTo.tolerance) ? offset.top + parseInt(scrollTo.tolerance): offset.top }, scrollTo.speed ? scrollTo.speed : 500);
        }, scrollTo.delay)
    }
}

/* --- selector function --- */

function element(m) {
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
