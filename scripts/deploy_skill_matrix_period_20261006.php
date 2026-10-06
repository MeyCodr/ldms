<?php
// One-shot deploy script for the skill matrix "fill the previous quarter"
// change (2026-10-06). The changed .php files stop deriving the quarter from
// evaluation_date and read/write skill_matrix_evaluations.eval_year /
// eval_quarter instead, so they fail until this has been run against the
// same database.
//
//   1. COLUMNS: add eval_year / eval_quarter to skill_matrix_evaluations.
//   2. BACKFILL: every existing row keeps the quarter it has always been
//      shown under, i.e. the quarter of its evaluation_date (a matrix filed
//      in Jul-Sep 2026 stays Q3 2026). Only rows created by the new code
//      follow the "previous quarter" rule in skill_matrix_period.php.
//
//      EXCEPT rows filed from 2026-10-01 until this script runs. The old
//      code saved those as Q4 2026, a quarter that under the new rule does
//      not open until 1 Jan 2027:
//        - staff with NO matrix filed in Jul-Sep 2026: the row becomes
//          their Q3 2026 matrix (it is one, just filed a few days late).
//        - staff who already HAVE a Q3 2026 matrix: the early Q4 row is
//          DELETED (decided 2026-10-06), so they are evaluated again from
//          January. Its topics and items go with it (ON DELETE CASCADE).
//   3. NOT NULL + INDEX: lock the columns down and index the lookup every
//      skill matrix page now does (staffid + period, and period alone).
//
// SAFETY
//   - Dry run by default: reports what each step would change, writes
//     nothing. Pass --apply to commit.
//   - Before anything is deleted, the rows (evaluation + topics + items)
//     are written to backup_skill_matrix_early_q4_<timestamp>.sql in this
//     directory as plain INSERTs; run that file to put them back. If the
//     backup cannot be written, step 2 stops without changing anything.
//   - Step 2 runs in one transaction: relabel, delete and backfill either
//     all commit or none do.
//   - Idempotent: only rows that have no period yet are ever touched, so a
//     re-run (or a run after the new code has gone live) changes nothing.
//
// USAGE
//  - CLI: php scripts/deploy_skill_matrix_period_20261006.php [--apply]
//  - HTTP, for cPanel hosts with no terminal/SSH access - upload this file
//    together with the changed .php files, then visit:
//      https://<your-domain>/ldms/scripts/deploy_skill_matrix_period_20261006.php?key=<token>
//      https://<your-domain>/ldms/scripts/deploy_skill_matrix_period_20261006.php?key=<token>&apply=1
//    Delete this file AND the backup .sql it writes from the server once
//    you're done (download the backup first).
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

// ===== STEP 2: backfill =====
echo "--- Step 2: backfill from evaluation_date ---\n";
$total = (int) $conn->query("SELECT COUNT(*) c FROM skill_matrix_evaluations")->fetch_assoc()['c'];
// Only rows without a period are in scope. Before step 1 has run that is
// every row; afterwards it is none, which is what keeps rows written by the
// new code (filed in October, period Q3) out of the early-Q4 handling below.
$missingWhere = $hasColumns ? "(sme.eval_year IS NULL OR sme.eval_quarter IS NULL)" : "1 = 1";
$missing = (int) $conn->query("SELECT COUNT(*) c FROM skill_matrix_evaluations sme WHERE {$missingWhere}")->fetch_assoc()['c'];
echo "Rows in table: {$total}\n";
echo "Rows needing a period: {$missing}\n";

$conn->begin_transaction();

$res = $conn->query("SELECT sme.id, sme.evaluation_date, sme.approval_status, u.staffno, u.staffname,
                            EXISTS (
                                SELECT 1
                                FROM skill_matrix_evaluations q3
                                WHERE q3.staffid = sme.staffid
                                AND q3.evaluation_date BETWEEN '2026-07-01' AND '2026-09-30'
                            ) AS has_q3
                     FROM skill_matrix_evaluations sme
                     LEFT JOIN user u ON u.id = sme.staffid
                     WHERE {$missingWhere}
                     AND sme.evaluation_date >= '2026-10-01'
                     ORDER BY sme.evaluation_date, sme.id");
$toRelabel = array();
$toDelete = array();
while ($row = $res->fetch_assoc()) {
    if ($row['has_q3']) {
        $toDelete[] = $row;
    } else {
        $toRelabel[] = $row;
    }
}

$earlyGroups = array(
    'become Q3 2026 (staff has no Q3 matrix)' => $toRelabel,
    'be DELETED (staff already has a Q3 matrix)' => $toDelete
);
foreach ($earlyGroups as $label => $rows) {
    echo "Rows filed since 2026-10-01 that " . ($apply ? "will " : "would ") . $label . ": " . count($rows) . "\n";
    foreach ($rows as $row) {
        $status = $row['approval_status'] === null ? 'DRAFT' : $row['approval_status'];
        echo "  -> id {$row['id']} | {$row['evaluation_date']} | {$status} | {$row['staffno']} | {$row['staffname']}\n";
    }
}

$relabelIds = implode(',', array_map(function ($row) { return (int) $row['id']; }, $toRelabel));
$deleteIds = implode(',', array_map(function ($row) { return (int) $row['id']; }, $toDelete));

if ($missing == 0) {
    $conn->rollback();
    echo "Nothing to do.\n";
} elseif (!$apply) {
    $conn->rollback();
    echo "Would give the remaining " . ($missing - count($toRelabel) - count($toDelete)) . " rows the quarter of their evaluation_date (re-run with --apply).\n";
} elseif (!$hasColumns) {
    $conn->rollback();
    echo "Skipping - step 1 did not complete.\n";
} else {
    $ok = true;

    if ($deleteIds !== '') {
        $backupPath = __DIR__ . '/backup_skill_matrix_early_q4_' . date('Ymd_His') . '.sql';
        $backupSql = "-- Skill matrix rows deleted by deploy_skill_matrix_period_20261006.php on " . date('Y-m-d H:i:s') . ".\n"
                   . "-- Filed 2026-10-01 onwards as Q4 2026 for staff who already had a Q3 2026 matrix.\n"
                   . "-- Run this file to restore them.\n";
        $backupSources = array(
            'skill_matrix_evaluations' => "SELECT * FROM skill_matrix_evaluations WHERE id IN ({$deleteIds}) ORDER BY id",
            'skill_matrix_topics' => "SELECT * FROM skill_matrix_topics WHERE evaluation_id IN ({$deleteIds}) ORDER BY id",
            'skill_matrix_items' => "SELECT i.* FROM skill_matrix_items i INNER JOIN skill_matrix_topics t ON t.id = i.topic_id WHERE t.evaluation_id IN ({$deleteIds}) ORDER BY i.id"
        );
        foreach ($backupSources as $table => $sql) {
            $backupRes = $conn->query($sql);
            while ($backupRow = $backupRes->fetch_assoc()) {
                // Not backfilled yet at this point; the old code meant these as Q4 2026.
                if ($table == 'skill_matrix_evaluations') {
                    $backupRow['eval_year'] = 2026;
                    $backupRow['eval_quarter'] = 4;
                }
                $values = array();
                foreach ($backupRow as $value) {
                    $values[] = $value === null ? 'NULL' : "'" . $conn->real_escape_string($value) . "'";
                }
                $backupSql .= "INSERT INTO `{$table}` (`" . implode('`, `', array_keys($backupRow)) . "`) VALUES (" . implode(', ', $values) . ");\n";
            }
        }

        if (file_put_contents($backupPath, $backupSql) === false) {
            $ok = false;
            echo "FAILED to write backup {$backupPath} - nothing changed.\n";
        } else {
            echo "Backup written: {$backupPath}\n";
            $ok = $conn->query("DELETE FROM skill_matrix_evaluations WHERE id IN ({$deleteIds})");
            echo $ok ? "Deleted {$conn->affected_rows} early Q4 rows.\n" : "FAILED to delete: {$conn->error}\n";
        }
    }

    if ($ok && $relabelIds !== '') {
        $ok = $conn->query("UPDATE skill_matrix_evaluations SET eval_year = 2026, eval_quarter = 3 WHERE id IN ({$relabelIds})");
        echo $ok ? "Set {$conn->affected_rows} rows to Q3 2026.\n" : "FAILED to relabel: {$conn->error}\n";
    }

    if ($ok) {
        $ok = $conn->query("UPDATE skill_matrix_evaluations
                            SET eval_year = YEAR(evaluation_date), eval_quarter = QUARTER(evaluation_date)
                            WHERE eval_year IS NULL OR eval_quarter IS NULL");
        echo $ok ? "Backfilled {$conn->affected_rows} rows from evaluation_date.\n" : "FAILED to backfill: {$conn->error}\n";
    }

    if ($ok) {
        $conn->commit();
        $missing = 0;
    } else {
        $conn->rollback();
        echo "Step 2 rolled back.\n";
    }
}
echo "\n";

// ===== STEP 3: NOT NULL + indexes =====
echo "--- Step 3: NOT NULL + indexes ---\n";
$yearColumn = $hasColumns ? smPeriodColumn($conn, 'eval_year') : null;
$quarterColumn = $hasColumns ? smPeriodColumn($conn, 'eval_quarter') : null;
$isNotNull = $yearColumn && $quarterColumn && $yearColumn['Null'] === 'NO' && $quarterColumn['Null'] === 'NO';
$hasStaffIndex = $hasColumns && smPeriodHasIndex($conn, 'idx_sme_staff_period');
$hasPeriodIndex = $hasColumns && smPeriodHasIndex($conn, 'idx_sme_period');

if ($isNotNull && $hasStaffIndex && $hasPeriodIndex) {
    echo "Already in place - nothing to do.\n";
} elseif (!$apply) {
    echo "Would set both columns NOT NULL and add idx_sme_staff_period / idx_sme_period (re-run with --apply).\n";
} elseif (!$hasColumns || $missing > 0) {
    echo "Skipping - steps 1 and 2 did not complete.\n";
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
    $ok = $conn->query("ALTER TABLE skill_matrix_evaluations " . implode(", ", $changes));
    echo $ok ? "Done.\n" : "FAILED: {$conn->error}\n";
}
echo "\n";

echo "==============================================================\n";
echo $apply ? "Done.\n" : "Dry run complete - nothing written. Re-run with --apply to commit.\n";
echo "==============================================================\n";
?>
