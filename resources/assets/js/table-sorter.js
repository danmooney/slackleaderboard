require('tablesorter');

var $tables = $('table');

$tables.each(function () {
    $(this).tablesorter({
        headers: {
            // disable sorting of the first & second column - before we would have to had made two entries
            // note that "first-name" is a class on the span INSIDE the first column th cell
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