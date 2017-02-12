<?php

namespace App\Helpers;

class TableRow
{
    private static $_has_invisible_rows = false;

    public function shouldBeInvisible($total_count_of_rows)
    {
        static $output_count = 0;
        static $times_called = 0;

        if ($times_called === 0) {
            static::$_has_invisible_rows = false;
        }

        $times_called += 1;

        $num_to_show_initially = config('app.options.tableSlider.numToShowInitially');
        $tolerance_threshold_for_when_at_end_of_list = config('app.options.tableSlider.toleranceThresholdForWhenAtEndOfList');

        if ($num_to_show_initially + $tolerance_threshold_for_when_at_end_of_list >= $total_count_of_rows) {
            $num_to_show_initially += $tolerance_threshold_for_when_at_end_of_list;
        }

        $should_be_invisible = $output_count + 1 > $num_to_show_initially;

        if (!$should_be_invisible) {
            $output_count += 1;
        } else {
            static::$_has_invisible_rows = true;
        }

        $should_reset_output_count = $times_called === $total_count_of_rows;

        if ($should_reset_output_count) {
            $times_called = 0;
            $output_count = 0;
        }

        return $should_be_invisible;
    }

    public function hasInvisibleRows()
    {
        return static::$_has_invisible_rows;
    }
}

return new TableRow();