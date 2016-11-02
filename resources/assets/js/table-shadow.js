var $reactionListTableCells = $('.table-cell-reaction-list');

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