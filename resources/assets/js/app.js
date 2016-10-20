
/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('tablesorter');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the body of the page. From here, you may begin adding components to
 * the application, or feel free to tweak this setup for your needs.
 */

var tablescroller_selector = $('.table-cell-reaction-list');

var tables = $('table');

tables.each(function() {
    console.log(this);
    $(this).tablesorter({
        headers: {
          // disable sorting of the first & second column - before we would have to had made two entries
          // note that "first-name" is a class on the span INSIDE the first column th cell
          '.nosort' : {
            // disable it by setting the property sorter to false
            sorter: false
          }
        }
    });
})

tablescroller_selector.each(function() {
    var parent_scroll = this.offsetWidth;

    if (($(this).children(0).get().scrollLeft + parent_scroll) != ($(this).children(0).get().scrollWidth + 10)) {
        $(this).addClass('table-cell-reaction-list--right-shadow');
    }

    $(this).children().eq(0).scroll(function() {

        var scroll_num = ((this.scrollLeft + parent_scroll) - (this.scrollWidth + 10));


        console.log(((this.scrollLeft + parent_scroll) - (this.scrollWidth + 10)));

        if (this.scrollLeft < 10) {
            $(this).parent().removeClass('table-cell-reaction-list--left-shadow');
        }

        if (this.scrollLeft > 10) {
            $(this).parent().addClass('table-cell-reaction-list--left-shadow');
        }

        if ( scroll_num > -10) {
            $(this).parent().removeClass('table-cell-reaction-list--right-shadow');
        }

        if ( scroll_num < -10) {
            $(this).parent().addClass('table-cell-reaction-list--right-shadow');
        }
    });
});
