<?php
// Shared save for the TNA forms (admin, clerk, HOD and staff tna_action.php).
//
// Every save replaces the owner's whole TNA: delete all of their rows for the
// year, then insert what the form posted. This used to be raw SQL built from
// $_POST with no escaping, so any apostrophe (a problem statement with
// "don't", or the "...SLA'S" training option) made the INSERT fail after the
// DELETE had already run, and the user's rows were silently lost. It also left
// the forms open to SQL injection.
//
// Now everything runs inside one transaction with prepared statements: either
// the full new TNA is saved, or nothing changes and the caller gets false.

require_once __DIR__ . '/dbconn.php';

// Form field suffix => tna.section, in form order.
const TNA_SAVE_SECTIONS = [
    'es' => 'esgaware',
    'se' => 'selfaware',
    'le' => 'leadaware',
    'da' => 'dataaware',
    'fu' => 'functional',
    'bu' => 'busiaware',
    'sp' => 'special',
];

// TNA is stored under the planning year - the same "FY" the form headings
// show, which rolls over on 1 October (see planning_year.php). The TNA lists
// and forms read the same year.
require_once __DIR__ . '/planning_year.php';

// $owner      columns that identify whose TNA this is, used for both the
//             DELETE and every INSERT: ['userid' => 12] or
//             ['grade' => 'E1', 'department' => 'HR'].
// $status     '1' (submitted) or 'APPROVE'.
// $approved   also stamp dateapprove = CURDATE().
// $extra      extra columns for one section's rows only, e.g.
//             ['esgaware' => ['department' => 'HR']].
// $post       the posted form ($_POST).
function tna_save_rows($conn, array $owner, $status, $approved, array $post, array $extra = [])
{
    // Throw on any SQL error so the catch below can roll back.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $year = (string) ldmsPlanningYear();
    try {
        $conn->begin_transaction();

        $where = implode(' AND ', array_map(fn($c) => "$c = ?", array_keys($owner)));
        $stmt = $conn->prepare("DELETE FROM tna WHERE $where AND year = ?");
        $params = array_merge(array_values($owner), [$year]);
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        $stmt->execute();
        $stmt->close();

        foreach (TNA_SAVE_SECTIONS as $p => $section) {
            $count = (int) ($post[$section] ?? 0);
            for ($i = 1; $i <= $count; $i++) {
                $field = fn($name) => trim((string) ($post[$name . $p . $i] ?? ''));
                $task = strtoupper($field('task'));
                $training = strtoupper($field('training'));
                if ($task === '' || $training === '') {
                    continue;
                }
                $target = tna_save_int($field('targetsk'));
                $current = tna_save_int($field('currentsk'));
                $gap = tna_save_int($field('gap'));
                // The gap box is filled in by the page's JS; if it arrived
                // blank, work it out instead of failing the insert.
                if ($gap === null && $target !== null && $current !== null) {
                    $gap = $target - $current;
                }

                $row = [
                    'task' => $task,
                    'training' => $training,
                    'othertr' => strtoupper($field('otr')),
                    'targetskill' => $target,
                    'currentskill' => $current,
                    'gap' => $gap,
                    'trainingtype' => strtoupper($field('trtype')),
                    'monthapply' => $field('datetr'),
                    'status' => $status,
                    'section' => $section,
                    'year' => $year,
                ] + $owner + ($extra[$section] ?? []);

                $cols = array_keys($row);
                $sql = "INSERT INTO tna (" . implode(',', $cols) . ($approved ? ',dateapprove' : '') . ") VALUES ("
                    . implode(',', array_fill(0, count($cols), '?')) . ($approved ? ',CURDATE()' : '') . ")";
                $stmt = $conn->prepare($sql);
                $vals = array_values($row);
                $stmt->bind_param(str_repeat('s', count($vals)), ...$vals);
                $stmt->execute();
                $stmt->close();
            }
        }

        $conn->commit();
        return true;
    } catch (Throwable $e) {
        $conn->rollback();
        error_log('tna_save_rows failed: ' . $e->getMessage());
        return false;
    }
}

function tna_save_int($v)
{
    return preg_match('/^-?\d+$/', $v) ? (int) $v : null;
}
