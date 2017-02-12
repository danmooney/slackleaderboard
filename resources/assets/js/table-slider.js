var numToShowInitially = 20;
var loadMoreIncrementNum = 20;
var toleranceThresholdForWhenAtEndOfList = 10;
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

    $showMoreButton = $('<button class="button-show-more btn-primary">Show More</button>');

    $showMoreButton.on('click', function (e) {
        currentlyShowingNum += loadMoreIncrementNum;

        if (currentlyShowingNum + toleranceThresholdForWhenAtEndOfList >= $bodyTrList.length) {
            currentlyShowingNum += toleranceThresholdForWhenAtEndOfList;
        }

        $bodyTrList.slice(0, currentlyShowingNum).show();


        debugger;
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

