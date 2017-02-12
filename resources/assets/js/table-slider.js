var numToShowInitially = SL_OPTIONS.numToShowInitially;
var loadMoreIncrementNum = SL_OPTIONS.loadMoreIncrementNum;
var toleranceThresholdForWhenAtEndOfList = SL_OPTIONS.toleranceThresholdForWhenAtEndOfList;
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

    $showMoreButton = $('<div><button class="button-show-more btn-primary">Show More</button></div>');

    $showMoreButton.on('click', function () {
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

