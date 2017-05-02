require('tablesorter');

var $tables = $('table');

$tables.each(function () {
    var $table = $(this),
        sortList,
        $th = $table.find('th'),
        $currentSortArrow = $th.find('.fa'),
        direction,
        directionNum,
        currentSortArrowHeaderColNum
    ;

    if ($currentSortArrow.length) {
        direction = $currentSortArrow.hasClass('fa-sort-desc') ? 'desc' : 'asc';
        directionNum = direction === 'desc' ? 1 : 0;
        currentSortArrowHeaderColNum = $th.index($currentSortArrow.closest('th'));
        sortList = [[currentSortArrowHeaderColNum, directionNum]];
    }

    $(this).tablesorter({
        sortInitialOrder: 'desc',
        sortRestart: true,
        sortList: sortList,
        headers: {
            '.nosort': {
                // disable it by setting the property sorter to false
                sorter: false
            }
        }
    }).on('sortEnd', function () {
        var $sortedTableHeader = $(this).find('.tablesorter-headerAsc, .tablesorter-headerDesc'),
            $sortArrow = $sortedTableHeader.find('.fa')
        ;

        // remove all current existing sort icons
        $(this).find('th .fa').removeClass('fa-sort-desc').removeClass('fa-sort-asc');

        if (!$sortArrow.length) {
            $sortArrow = $('<i class="fa fa-fw"></i>');
            $sortArrow.appendTo($sortedTableHeader.children('.tablesorter-header-inner'));
        }

        $sortArrow.addClass($sortedTableHeader.hasClass('tablesorter-headerAsc') ? 'fa-sort-asc' : 'fa-sort-desc');
    });
});