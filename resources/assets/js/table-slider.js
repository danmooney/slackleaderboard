var options = SL_OPTIONS.tableSlider;
var numToShowInitially = options.numToShowInitially;
var loadMoreIncrementNum = options.loadMoreIncrementNum;
var toleranceThresholdForWhenAtEndOfList = options.toleranceThresholdForWhenAtEndOfList;
var currentlyShowingNum = numToShowInitially;

var $tables = $('table');

$tables.each(function () {
    var $table = $(this),
        $bodyTrList = $table.find('tbody tr'),
        needToHideSomeRows = $bodyTrList.length > numToShowInitially,
        $showMoreButton,
        canHideShowMoreButton;

    if (!needToHideSomeRows) {
        return;
    }

    if (currentlyShowingNum + toleranceThresholdForWhenAtEndOfList >= $bodyTrList.length) {
        currentlyShowingNum += toleranceThresholdForWhenAtEndOfList;
    }

    $bodyTrList.slice(currentlyShowingNum).hide();

    $showMoreButton = $table.next('.button-show-more-container');

    $showMoreButton.on('click', function () {
        // need to get refreshed list since it may be out of order from the user's sorting (jQuery persists order from its original invocation)
        $bodyTrList = $table.find('tbody tr');
        currentlyShowingNum += loadMoreIncrementNum;

        if (currentlyShowingNum + toleranceThresholdForWhenAtEndOfList >= $bodyTrList.length) {
            currentlyShowingNum += toleranceThresholdForWhenAtEndOfList;
        }

        $bodyTrList.slice(0, currentlyShowingNum).show();

        canHideShowMoreButton = currentlyShowingNum >= $bodyTrList.length;

        if (canHideShowMoreButton) {
            $(this).hide();
        }
    });

    $showMoreButton.insertAfter($table);

    $table.on('sortEnd', showRowsBeforeCurrentlyShowingNumAndHideRowsAfterCurrentlyShowingNum);
});

function showRowsBeforeCurrentlyShowingNumAndHideRowsAfterCurrentlyShowingNum() {
    var $table = $(this),
        $bodyTrList = $table.find('tbody tr');

    $bodyTrList.slice(0, currentlyShowingNum).show();
    $bodyTrList.slice(currentlyShowingNum).hide();
}

