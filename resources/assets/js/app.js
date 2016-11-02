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

var $reactionListTableCells = $('.table-cell-reaction-list');

var $tables = $('table');

$tables.each(function () {
    console.log(this);
    $(this).tablesorter({
        headers: {
            // disable sorting of the first & second column - before we would have to had made two entries
            // note that "first-name" is a class on the span INSIDE the first column th cell
            '.nosort': {
                // disable it by setting the property sorter to false
                sorter: false
            }
        }
    });
});

$reactionListTableCells.each(function () {
    var tableCellContainerVisibleWidth = this.clientWidth;
    var $tableCellContents = $(this).children(0);
    var tableCellContents = $tableCellContents.get(0);

    if (tableCellContents.scrollLeft + tableCellContainerVisibleWidth !== tableCellContents.scrollWidth + 10) {
        $(this).addClass('table-cell-reaction-list--right-shadow');
    }

    $tableCellContents.scroll(function () {
        var scrollNum = (this.scrollLeft + tableCellContainerVisibleWidth) - (this.scrollWidth + 10);
        var $parent = $(this).parent();

        console.log(scrollNum);

        if (this.scrollLeft < 10) {
            $parent.removeClass('table-cell-reaction-list--left-shadow');
        }

        if (this.scrollLeft > 10) {
            $parent.addClass('table-cell-reaction-list--left-shadow');
        }

        if (scrollNum > -10) {
            $parent.removeClass('table-cell-reaction-list--right-shadow');
        }

        if (scrollNum < -10) {
            $parent.addClass('table-cell-reaction-list--right-shadow');
        }
    });
});
