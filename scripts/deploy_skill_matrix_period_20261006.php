<?php
// One-shot deploy script for the skill matrix "fill the previous quarter"
// change (2026-10-06). The changed .php files stop deriving the quarter from
// evaluation_date and read/write skill_matrix_evaluations.eval_year /
// eval_quarter instead, so they fail until this has been run against the
// same database.
//
// THE RULE: a skill matrix always belongs to the quarter BEFORE the one it
// was filed in (see skill_matrix_period.php). That holds for the existing
// records too - confirmed 2026-10-06: everything filed in Jul-Sep 2026 is the
// Q2 2026 evaluation, and what has been filed since 1 Oct 2026 is Q3 2026.
//
//   1. COLUMNS: add eval_year / eval_quarter to skill_matrix_evaluations.
//   2. RESTORE: an earlier version of this script (same day) worked from the
//      opposite reading - Jul-Sep filings as Q3 - and so DELETED the rows
//      filed since 1 Oct for staff who already had a Jul-Sep matrix, after
//      saving them to backup_skill_matrix_early_q4_<timestamp>.sql in this
//      directory. Those rows are legitimate Q3 2026 matrices. If such a
//      backup file is found here, its rows are put back. On a database the
//      earlier version never ran against there is no backup file and this
//      step does nothing.
//   3. PERIOD: every row gets the quarter before the one its evaluation_date
//      falls in. Rows that already carry the right period are left alone.
//   4. NOT NULL + INDEX: lock the columns down and index the lookup every
//      skill matrix page now does (staffid + period, and period alone).
//
// SAFETY
//   - Dry run by default: reports what each step would change, writes
//     nothing. Pass --apply to commit.
//   - This version deletes nothing.
//   - Steps 2 and 3 run in one transaction: restore and relabel either both
//     commit or neither does.
//   - A backed-up row is NOT restored if its id is in the table already, or
//     if that staff has since been given another matrix filed on/after
//     2026-10-01 (restoring would leave them with two Q3 matrices). Those
//     are listed for a decision by hand.
//   - Idempotent: a re-run finds nothing left to restore or relabel.
//
// USAGE
//  - CLI: php scripts/deploy_skill_matrix_period_20261006.php [--apply]
//  - HTTP, for cPanel hosts with no terminal/SSH access - upload this file
//    together with the changed .php files, then visit:
//      https://<your-domain>/ldms/scripts/deploy_skill_matrix_period_20261006.php?key=<token>
//      https://<your-domain>/ldms/scripts/deploy_skill_matrix_period_20261006.php?key=<token>&apply=1
//    Delete this file (and any backup .sql beside it, after downloading it)
//    from the server once you're done.
define('DEPLOY_SM_PERIOD_TOKEN', '9c1e47a2d05b83f6e7310ab4c85d2f96b0473e1a5cd8f237');

if (PHP_SAPI !== 'cli') {
    if (!isset($_GET['key']) || !hash_equals(DEPLOY_SM_PERIOD_TOKEN, (string) $_GET['key'])) {
        http_response_code(403);
        header('Content-Type: text/plain');
        die('Forbidden');
    }
    header('Content-Type: text/plain');
    @set_time_limit(0);
    @ignore_user_abort(true);
}

require __DIR__ . '/../dbconn.php';

$apply = (PHP_SAPI === 'cli')
    ? in_array('--apply', $argv, true)
    : (isset($_GET['apply']) && $_GET['apply'] === '1');

echo "==============================================================\n";
echo "Skill matrix period columns deploy - " . ($apply ? "APPLYING" : "DRY RUN") . "\n";
echo "==============================================================\n\n";

function smPeriodColumn($conn, $name)
{
    return $conn->query("SHOW COLUMNS FROM skill_matrix_evaluations WHERE Field = '{$name}'")->fetch_assoc();
}

function smPeriodHasIndex($conn, $name)
{
    return $conn->query("SHOW INDEX FROM skill_matrix_evaluations WHERE Key_name = '{$name}'")->num_rows > 0;
}

// The quarter before the one evaluation_date falls in, as SQL.
$expectedYear = "IF(QUARTER(evaluation_date) = 1, YEAR(evaluation_date) - 1, YEAR(evaluation_date))";
$expectedQuarter = "IF(QUARTER(evaluation_date) = 1, 4, QUARTER(evaluation_date) - 1)";

// ===== STEP 1: columns =====
echo "--- Step 1: eval_year / eval_quarter columns ---\n";
$hasColumns = smPeriodColumn($conn, 'eval_year') && smPeriodColumn($conn, 'eval_quarter');
echo $hasColumns ? "Already present - nothing to do.\n" : "Missing.\n";

if (!$hasColumns) {
    if ($apply) {
        $ok = $conn->query("ALTER TABLE skill_matrix_evaluations
                            ADD COLUMN eval_year SMALLINT UNSIGNED NULL AFTER evaluation_date,
                            ADD COLUMN eval_quarter TINYINT UNSIGNED NULL AFTER eval_year");
        echo $ok ? "Columns added.\n" : "FAILED to add columns: {$conn->error}\n";
        $hasColumns = (bool) $ok;
    } else {
        echo "Would add them (re-run with --apply).\n";
    }
}
echo "\n";

$conn->begin_transaction();
$ok = true;

// ===== STEP 2: restore rows the earlier version deleted =====
echo "--- Step 2: restore rows deleted by the earlier version of this script ---\n";
$backupFiles = glob(__DIR__ . '/backup_skill_matrix_early_q4_*.sql');
$restoreStatements = array();
$restoreCount = 0;

if (!$backupFiles) {
    echo "No backup_skill_matrix_early_q4_*.sql here - nothing to restore.\n";
}

foreach ($backupFiles ?: array() as $backupFile) {
    // Every line is one INSERT whose first two values are numeric ids:
    //   evaluations (id, staffid, ...), topics (id, evaluation_id, ...), items (id, topic_id, ...)
    $evaluations = array();
    $topicEvaluation = array();
    $childLines = array();

    foreach (file($backupFile, FILE_IGNORE_NEW_LINES) as $line) {
        if (!preg_match("/^INSERT INTO `(skill_matrix_\w+)` \(`id`, `(\w+)`.*?\) VALUES \('(\d+)', '(\d+)'/", $line, $m)) {
            continue;
        }
        $line = rtrim($line, ';');

        if ($m[1] == 'skill_matrix_evaluations' && $m[2] == 'staffid') {
            $evaluations[(int) $m[3]] = array('staffid' => (int) $m[4], 'line' => $line);
        } elseif ($m[1] == 'skill_matrix_topics' && $m[2] == 'evaluation_id') {
            $topicEvaluation[(int) $m[3]] = (int) $m[4];
            $childLines[(int) $m[4]][] = $line;
        } elseif ($m[1] == 'skill_matrix_items' && $m[2] == 'topic_id' && isset($topicEvaluation[(int) $m[4]])) {
            $childLines[$topicEvaluation[(int) $m[4]]][] = $line;
        }
    }

    echo basename($backupFile) . ": " . count($evaluations) . " skill matrices\n";

    foreach ($evaluations as $evaluationId => $evaluation) {
        if ($conn->query("SELECT 1 FROM skill_matrix_evaluations WHERE id = {$evaluationId}")->num_rows > 0) {
            continue;
        }

        $newer = $conn->query("SELECT id, evaluation_date FROM skill_matrix_evaluations WHERE staffid = {$evaluation['staffid']} AND evaluation_date >= '2026-10-01' ORDER BY id LIMIT 1")->fetch_assoc();
        if ($newer) {
            echo "  -> NOT restoring id {$evaluationId}: staff {$evaluation['staffid']} already has matrix id {$newer['id']} filed {$newer['evaluation_date']} - decide by hand\n";
            continue;
        }

        $restoreStatements[] = $evaluation['line'];
        foreach (isset($childLines[$evaluationId]) ? $childLines[$evaluationId] : array() as $childLine) {
            $restoreStatements[] = $childLine;
        }
        $restoreCount++;
    }
}

if ($backupFiles) {
    if ($restoreCount == 0) {
        echo "Nothing left to restore.\n";
    } elseif (!$apply) {
        echo "Would restore {$restoreCount} skill matrices with their topics and ratings (re-run with --apply).\n";
    } elseif (!$hasColumns) {
        $ok = false;
        echo "Skipping - step 1 did not complete.\n";
    } else {
        foreach ($restoreStatements as $statement) {
            if (!$conn->query($statement)) {
                $ok = false;
                echo "FAILED to restore: {$conn->error}\n";
                break;
            }
        }
        if ($ok) {
            echo "Restored {$restoreCount} skill matrices with their topics and ratings.\n";
        }
    }
}
echo "\n";

// ===== STEP 3: period =====
echo "--- Step 3: period = the quarter before the one the matrix was filed in ---\n";
$total = (int) $conn->query("SELECT COUNT(*) c FROM skill_matrix_evaluations")->fetch_assoc()['c'];
$currentPeriod = $hasColumns ? "CONCAT('Q', eval_quarter, ' ', eval_year)" : "NULL";
$wrongWhere = $hasColumns
    ? "(eval_year IS NULL OR eval_quarter IS NULL OR eval_year <> {$expectedYear} OR eval_quarter <> {$expectedQuarter})"
    : "1 = 1";
$wrong = (int) $conn->query("SELECT COUNT(*) c FROM skill_matrix_evaluations WHERE {$wrongWhere}")->fetch_assoc()['c'];
echo "Rows in table: {$total}\n";
echo "Rows needing their period set or corrected: {$wrong}\n";

$res = $conn->query("SELECT COALESCE({$currentPeriod}, 'none') AS from_period,
                            CONCAT('Q', {$expectedQuarter}, ' ', {$expectedYear}) AS to_period,
                            COUNT(*) n, MIN(evaluation_date) mn, MAX(evaluation_date) mx
                     FROM skill_matrix_evaluations
                     WHERE {$wrongWhere}
                     GROUP BY from_period, to_period
                     ORDER BY mn");
while ($row = $res->fetch_assoc()) {
    echo "  -> {$row['n']} rows filed {$row['mn']} to {$row['mx']}: {$row['from_period']} -> {$row['to_period']}\n";
}

if ($wrong > 0) {
    if (!$apply) {
        echo "Would update {$wrong} rows (re-run with --apply).\n";
    } elseif (!$hasColumns || !$ok) {
        $ok = false;
        echo "Skipping - an earlier step did not complete.\n";
    } else {
        $ok = $conn->query("UPDATE skill_matrix_evaluations
                            SET eval_year = {$expectedYear}, eval_quarter = {$expectedQuarter}
                            WHERE {$wrongWhere}");
        echo $ok ? "Updated {$conn->affected_rows} rows.\n" : "FAILED to update: {$conn->error}\n";
    }
}

if ($apply && $ok) {
    $conn->commit();
    $wrong = 0;
} else {
    $conn->rollback();
    if ($apply) {
        echo "Steps 2 and 3 rolled back.\n";
    }
}
echo "\n";

// ===== STEP 4: NOT NULL + indexes =====
echo "--- Step 4: NOT NULL + indexes ---\n";
$yearColumn = $hasColumns ? smPeriodColumn($conn, 'eval_year') : null;
$quarterColumn = $hasColumns ? smPeriodColumn($conn, 'eval_quarter') : null;
$isNotNull = $yearColumn && $quarterColumn && $yearColumn['Null'] === 'NO' && $quarterColumn['Null'] === 'NO';
$hasStaffIndex = $hasColumns && smPeriodHasIndex($conn, 'idx_sme_staff_period');
$hasPeriodIndex = $hasColumns && smPeriodHasIndex($conn, 'idx_sme_period');

if ($isNotNull && $hasStaffIndex && $hasPeriodIndex) {
    echo "Already in place - nothing to do.\n";
} elseif (!$apply) {
    echo "Would set both columns NOT NULL and add idx_sme_staff_period / idx_sme_period (re-run with --apply).\n";
} elseif (!$hasColumns || !$ok || $wrong > 0) {
    echo "Skipping - earlier steps did not complete.\n";
} else {
    $changes = array();
    if (!$isNotNull) {
        $changes[] = "MODIFY eval_year SMALLINT UNSIGNED NOT NULL";
        $changes[] = "MODIFY eval_quarter TINYINT UNSIGNED NOT NULL";
    }
    if (!$hasStaffIndex) {
        $changes[] = "ADD INDEX idx_sme_staff_period (staffid, eval_year, eval_quarter)";
    }
    if (!$hasPeriodIndex) {
        $changes[] = "ADD INDEX idx_sme_period (eval_year, eval_quarter)";
    }
    $changed = $conn->query("ALTER TABLE skill_matrix_evaluations " . implode(", ", $changes));
    echo $changed ? "Done.\n" : "FAILED: {$conn->error}\n";
}
echo "\n";

// ===== RESULT =====
echo "--- Skill matrices per quarter" . ($apply ? "" : " (as the table stands now, before any change)") . " ---\n";
$periodYear = $apply && $hasColumns ? "eval_year" : $expectedYear;
$periodQuarter = $apply && $hasColumns ? "eval_quarter" : $expectedQuarter;
if (!$apply) {
    echo "(counted by the quarter each row WILL have)\n";
}
$res = $conn->query("SELECT {$periodYear} y, {$periodQuarter} q,
                            SUM(approval_status = 'APPROVED') approved,
                            SUM(approval_status = 'PENDING') pending,
                            SUM(approval_status IS NULL) draft
                     FROM skill_matrix_evaluations
                     GROUP BY y, q
                     ORDER BY y, q");
while ($row = $res->fetch_assoc()) {
    echo "  Q{$row['q']} {$row['y']}: {$row['approved']} approved, {$row['pending']} waiting approval, {$row['draft']} draft\n";
}

$res = $conn->query("SELECT sme.staffid, u.staffno, u.staffname, {$periodYear} y, {$periodQuarter} q, COUNT(*) n, GROUP_CONCAT(sme.id ORDER BY sme.id) ids
                     FROM skill_matrix_evaluations sme
                     LEFT JOIN user u ON u.id = sme.staffid
                     GROUP BY sme.staffid, y, q
                     HAVING COUNT(*) > 1");
echo "Staff with more than one matrix in the same quarter: {$res->num_rows}\n";
while ($row = $res->fetch_assoc()) {
    echo "  -> {$row['staffno']} | {$row['staffname']} | Q{$row['q']} {$row['y']} | ids {$row['ids']}\n";
}
echo "\n";

echo "==============================================================\n";
echo $apply ? "Done.\n" : "Dry run complete - nothing written. Re-run with --apply to commit.\n";
echo "==============================================================\n";
?>
