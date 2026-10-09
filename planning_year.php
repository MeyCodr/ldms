<?php
// TNA / TNI forms are filled in for the coming year once planning starts in
// October, so the "FY" they show (and the year TNI records are stored under)
// rolls over on 1 October instead of 1 January.
//
//   Today in Jan-Sep -> this year
//   Today in Oct-Dec -> next year
//
// Returns the year as an int, e.g. 2027.
if (!function_exists('ldmsPlanningYear')) {
    function ldmsPlanningYear($timestamp = null)
    {
        $date = new DateTime('now', new DateTimeZone('Asia/Kuala_Lumpur'));
        if ($timestamp !== null) {
            $date->setTimestamp($timestamp);
        }

        $year = (int) $date->format('Y');

        return (int) $date->format('n') >= 10 ? $year + 1 : $year;
    }
}

// The tna (or tni) table narrowed to the planning year, for dropping into a
// query in place of the table name:
//   "select ... from " . ldmsPlanningYearTable() . " tna where ..."
// Summaries and exports use it so they start empty again on 1 October, the
// same day the forms do.
if (!function_exists('ldmsPlanningYearTable')) {
    function ldmsPlanningYearTable($table = 'tna')
    {
        $table = $table === 'tni' ? 'tni' : 'tna';

        return "(select * from $table where year = '" . ldmsPlanningYear() . "')";
    }
}
?>
