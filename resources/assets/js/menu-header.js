$('nav .list').on('mouseenter', function () {
    $(this).addClass('hover');
}).on('mouseleave', _.debounce(function () {
    $(this).removeClass('hover');
}, 250));