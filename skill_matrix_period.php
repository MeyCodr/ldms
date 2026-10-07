<?php
// The skill matrix is filled one quarter in arrears: the quarter that can be
// filled (and that every skill matrix list, chart and export shows) is the
// one that has just ended, not the one today's date falls in.
//
//   Today in Jan-Mar -> Q4 of the previous year
//   Today in Apr-Jun -> Q1
//   Today in Jul-Sep -> Q2
//   Today in Oct-Dec -> Q3
//
// The period is stored on each row in skill_matrix_evaluations.eval_year /
// eval_quarter; evaluation_date is only the day the matrix was filled.
//
// Returns array($year, $quarter).
function skillMatrixFillPeriod($timestamp = null)
{
    $timestamp = $timestamp === null ? time() : $timestamp;
    $year = (int) date('Y', $timestamp);
    $quarter = (int) ceil(date('n', $timestamp) / 3) - 1;

    if ($quarter < 1) {
        $quarter = 4;
        $year--;
    }

    return array($year, $quarter);
}

// Months covered by a quarter, e.g. 3 -> "July - September".
function skillMatrixQuarterMonths($quarter)
{
    $months = array(1 => 'January - March', 2 => 'April - June', 3 => 'July - September', 4 => 'October - December');

    return isset($months[(int) $quarter]) ? $months[(int) $quarter] : '';
}

// The quarter before the given one. Returns array($year, $quarter).
function skillMatrixPreviousPeriod($year, $quarter)
{
    return $quarter > 1 ? array((int) $year, $quarter - 1) : array($year - 1, 4);
}

// Ids of every staff who has a skill matrix for a period, as array(staffid => true).
function skillMatrixStaffIdsWithEvaluation($conn, $year, $quarter)
{
    $staffIds = array();
    $stmt = $conn->prepare("SELECT DISTINCT staffid FROM skill_matrix_evaluations WHERE eval_year = ? AND eval_quarter = ?");
    $stmt->bind_param("ii", $year, $quarter);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $staffIds[(int) $row['staffid']] = true;
    }

    return $staffIds;
}
?>
