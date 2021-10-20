notify = function (text, style, time = '10000', callback = null, close_callback = null) {

    var $container = $('#notifications');
    var icon = '<i class="fa fa-info-circle "></i>';

    if (typeof style == 'undefined') style = 'warning'

    var html = $('<div class="border border-primary alert alert-' + style + '  hide">' + icon + " " + text + '</div>');

    $('<a>', {
        text: '×',
        class: 'button close',
        style: 'padding-left: 10px;',
        href: '#',
        click: function (e) {
            e.preventDefault()
            close_callback && close_callback()
            remove_notice()
        }
    }).prependTo(html)

    $container.prepend(html)
    html.removeClass('hide').hide().fadeIn('slow')

    function remove_notice() {
        html.stop().fadeOut('slow').remove()
    }

    function set_interval() {
        if (time != '0') {
            return setInterval(remove_notice, time);
        }

        return null;
    }

    var timer = set_interval();

    $(html).hover(function () {
        clearInterval(timer);
    }, function () {
        timer = set_interval(remove_notice, time);
    });

    html.on('click', function () {
        clearInterval(timer)
        callback && callback()
        remove_notice()
    });


}