<?php
// One-shot production deploy script for the PME "not_applicable" fix built
// and verified locally on 2026-09-15, but only ever applied to the LOCAL
// database - uploading the changed .php files (fetch_training.php x4,
// view_pme.php) is not enough on its own, because none of what makes those
// new code branches actually fire lives in a .php file:
//
//   1. ENUM: pme.status needs 'not_applicable' added, or any UPDATE/INSERT
//      that tries to write it is rejected by MySQL.
//   2. TRIGGER: after_participation_insert (AFTER INSERT ON participation)
//      is rewritten so a newly-added participant who can never actually be
//      evaluated - wrong designation, a HOD, a Head of Division, no HOD
//      assigned, or resigned - gets inserted as 'not_applicable' instead of
//      'pending'. Without this, every NEW training participant keeps
//      generating the exact same stuck row this whole fix is for.
//   3. BACKFILL: every EXISTING 'pending' row that can never be evaluated
//      (920 of them, found while investigating M0734 / ADIL AFFANDY BIN
//      OTHMAN seeing "Waiting for HOD evaluation" forever as a Head of
//      Division) gets moved to 'not_applicable' too.
//
// Reachability rule (mirrors staff/hod/pme/pme.php and
// scripts/notify_pme_incomplete.php exactly, including how it treats NULL
// user.status and NULL user.usertype - both were found to already be
// treated as "not reachable" by the existing HOD-list query, so this
// script matches that rather than being more lenient):
//   designation = 'Executive'
//   OR (designation = 'MANAGER (AM/HOS & ABOVE)'
//       AND usertype <> 'HOD'          -- NULL usertype does NOT pass this
//       AND not a Head of Division)
//   AND user.status <> 'RESIGN'        -- NULL status does NOT pass this
//   AND hodid is a real, non-zero user id
//
// SAFETY
//   - Dry run by default: reports exactly what each step would change,
//     writes nothing. Pass --apply to actually commit all three steps.
//   - Step 3's exact row list is written to an undo-map TSV in this
//     directory BEFORE any UPDATE runs.
//   - Steps 1/2 are idempotent - safe to run again if step 3 fails; each
//     step is skipped if it's already in the target state.
//   - M0734's own 5 rows were already hand-fixed to 'completed' on
//     whichever database that was run against - this script only ever
//     touches rows still at 'pending', so it will not touch those again.
//
// USAGE
//  - CLI (php scripts/deploy_pme_not_applicable_20260915.php [--apply]) -
//    no token needed, CLI access already implies server access.
//  - HTTP, for cPanel hosts with no terminal/SSH access - upload this file
//    normally, then visit it in a browser with the matching ?key=:
//      https://<your-domain>/ldms/scripts/deploy_pme_not_applicable_20260915.php?key=4879ff755e8635b1d9d588ca29282d46637b5df07d89353b
//      https://<your-domain>/ldms/scripts/deploy_pme_not_applicable_20260915.php?key=...&apply=1
//    Change DEPLOY_PME_TOKEN below before relying on this - the value here
//    is a placeholder generated for setup, not a secret kept out of source
//    control. Delete this file from the server once you're done with it;
//    it is a one-time fix, not something that needs to stay reachable.
define('DEPLOY_PME_TOKEN', '4879ff755e8635b1d9d588ca29282d46637b5df07d89353b');

if (PHP_SAPI !== 'cli') {
    if (!isset($_GET['key']) || !hash_equals(DEPLOY_PME_TOKEN, (string) $_GET['key'])) {
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
echo "PME not_applicable fix deploy - " . ($apply ? "APPLYING" : "DRY RUN") . "\n";
echo "==============================================================\n\n";

// ===== STEP 1: enum =====
echo "--- Step 1: pme.status ENUM ---\n";
$col = $conn->query("SHOW COLUMNS FROM pme WHERE Field = 'status'")->fetch_assoc();
$hasEnumValue = strpos($col['Type'], "'not_applicable'") !== false;
echo $hasEnumValue ? "Already has 'not_applicable' - nothing to do.\n" : "Missing 'not_applicable' in the enum.\n";

if (!$hasEnumValue) {
    if ($apply) {
        $ok = $conn->query("ALTER TABLE pme MODIFY status ENUM('pending','approved','completed','verified','not_applicable') DEFAULT 'pending'");
        echo $ok ? "Enum altered.\n" : "FAILED to alter enum: {$conn->error}\n";
        if ($ok) {
            $hasEnumValue = true; // so step 2 below sees the up-to-date state within this same run
        }
    } else {
        echo "Would alter the enum to add it (re-run with --apply).\n";
    }
}
echo "\n";

// ===== STEP 2: trigger =====
echo "--- Step 2: after_participation_insert trigger ---\n";
$existing = $conn->query("SHOW TRIGGERS WHERE `Table` = 'participation' AND `Trigger` = 'after_participation_insert'");
$hasFix = false;
if ($existing && $row = $existing->fetch_assoc()) {
    $hasFix = strpos($row['Statement'], 'not_applicable') !== false;
}
echo $hasFix ? "Already has the reachability check - nothing to do.\n" : "Missing the reachability check (still inserts every row as 'pending').\n";

$triggerSql = "CREATE TRIGGER after_participation_insert AFTER INSERT ON participation FOR EACH ROW
BEGIN
    DECLARE v_staffname VARCHAR(255);
    DECLARE v_staffno VARCHAR(50);
    DECLARE v_department VARCHAR(255);
    DECLARE v_hodid INT;
    DECLARE v_designation VARCHAR(255);
    DECLARE v_usertype VARCHAR(255);
    DECLARE v_userstatus VARCHAR(50);
    DECLARE v_training_title VARCHAR(255);
    DECLARE v_from_date DATE;
    DECLARE v_to_date DATE;
    DECLARE v_is_division_head INT DEFAULT 0;
    DECLARE v_hod_exists INT DEFAULT 0;
    DECLARE v_status VARCHAR(20) DEFAULT 'not_applicable';

    SELECT staffname, staffno, department, hodid, designation, usertype, status
    INTO v_staffname, v_staffno, v_department, v_hodid, v_designation, v_usertype, v_userstatus
    FROM user
    WHERE id = NEW.userid;

    SELECT title, DATE_ADD(startdate, INTERVAL 1 DAY)
    INTO v_training_title, v_from_date
    FROM training
    WHERE id = NEW.trainingid;

    SET v_to_date = DATE_ADD(v_from_date, INTERVAL 3 MONTH);

    SELECT COUNT(*) INTO v_is_division_head FROM divisions WHERE head_user_id = NEW.userid;
    SELECT COUNT(*) INTO v_hod_exists FROM user WHERE id = v_hodid;

    IF v_userstatus <> 'RESIGN'
       AND v_hodid IS NOT NULL AND v_hodid <> 0 AND v_hod_exists > 0
       AND (
           v_designation = 'Executive'
           OR (
               v_designation = 'MANAGER (AM/HOS & ABOVE)'
               AND v_usertype <> 'HOD'
               AND v_is_division_head = 0
           )
       )
    THEN
        SET v_status = 'pending';
    END IF;

    INSERT INTO pme (
        participationid, userid, trainingid, staffname, staffno, department, hodid, designation,
        training_title, from_date, to_date,
        level_rating, level_percent, level_remark,
        level_rating2, level_percent2, level_remark2,
        behavioral_rating, behavioral_percent, behavioral_remark,
        result_rating, result_percent, result_remark,
        status
    ) VALUES (
        NEW.id, NEW.userid, NEW.trainingid, v_staffname, v_staffno, v_department, v_hodid, v_designation,
        v_training_title, v_from_date, v_to_date,
        NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,
        v_status
    );
END";

if (!$hasFix) {
    if ($apply) {
        if (!$hasEnumValue) {
            echo "Skipping trigger - step 1 (enum) has not been applied yet, and this trigger can write 'not_applicable'.\n";
        } else {
            $conn->query("DROP TRIGGER IF EXISTS after_participation_insert");
            $ok = $conn->query($triggerSql);
            echo $ok ? "Trigger recreated with the reachability check.\n" : "FAILED to recreate trigger: {$conn->error}\n";
        }
    } else {
        echo "Would recreate it with the reachability check (re-run with --apply).\n";
    }
}
echo "\n";

// ===== STEP 3: backfill existing stuck rows =====
echo "--- Step 3: backfill existing unreachable 'pending' rows ---\n";

$reachableSubquery = "
    SELECT p2.id
    FROM pme p2
    JOIN user u2 ON p2.userid = u2.id
    JOIN user h2 ON h2.id = p2.hodid
    WHERE p2.status = 'pending'
      AND u2.status != 'RESIGN'
      AND (
          p2.designation = 'Executive'
          OR (
              p2.designation = 'MANAGER (AM/HOS & ABOVE)'
              AND u2.usertype != 'HOD'
              AND NOT EXISTS (SELECT 1 FROM divisions dv WHERE dv.head_user_id = u2.id)
          )
      )
";

// This is a live, actively-used database, so the three reads below (total /
// reachable / to-fix) are wrapped in one transaction to get a single
// consistent snapshot - InnoDB's default REPEATABLE READ isolation means
// every plain SELECT inside this transaction sees the data as it stood at
// the FIRST read, so a real user adding a training participant mid-script
// can no longer make the counts disagree with each other. The eventual
// UPDATE (if --apply) stays in the same transaction so the write applies to
// exactly the snapshot that was verified, then everything commits together.
$conn->begin_transaction();

$totalPending = (int) $conn->query("SELECT COUNT(*) c FROM pme WHERE status='pending'")->fetch_assoc()['c'];
$reachable = (int) $conn->query("SELECT COUNT(*) c FROM ($reachableSubquery) x")->fetch_assoc()['c'];

// LEFT JOIN (not JOIN): a pme row whose userid no longer matches any real
// user row (e.g. a hard-deleted user) must still be picked up here. An
// inner join would silently drop it from BOTH this query and $reachable
// above - counted in $totalPending, absent from both other counts - which
// is exactly the 1-row gap production hit on 2026-09-16. Such a row is
// unreachable by definition (no user to route it to), so it belongs here.
$res = $conn->query("
    SELECT p.id, p.staffname, p.staffno, p.training_title, p.designation, p.hodid, u.usertype, u.status AS user_status
    FROM pme p
    LEFT JOIN user u ON u.id = p.userid
    WHERE p.status = 'pending' AND p.id NOT IN ($reachableSubquery)
");
$toFix = [];
while ($row = $res->fetch_assoc()) { $toFix[] = $row; }

echo "Total pending: $totalPending\n";
echo "Reachable (a real HOD will see it): $reachable\n";
echo "Rows that would move to not_applicable: " . count($toFix) . "\n";

if (count($toFix) + $reachable !== $totalPending) {
    $conn->rollback();
    echo "SANITY CHECK FAILED (counts don't add up) - stopping without touching step 3. This should no longer happen now that the reads are snapshotted together; if it does, something other than ordinary concurrent use is going on and it's worth a closer look before re-running.\n";
} elseif (!empty($toFix)) {
    $backupPath = __DIR__ . '/backup_pme_not_applicable_' . date('Ymd_His') . '_undo_map.tsv';
    $fh = fopen($backupPath, 'w');
    fwrite($fh, "pme_id\tstaffname\tstaffno\ttraining_title\tdesignation\thodid\tusertype\tuser_status\told_status\tnew_status\n");
    foreach ($toFix as $r) {
        fwrite($fh, "{$r['id']}\t{$r['staffname']}\t{$r['staffno']}\t{$r['training_title']}\t{$r['designation']}\t{$r['hodid']}\t{$r['usertype']}\t{$r['user_status']}\tpending\tnot_applicable\n");
    }
    fclose($fh);
    echo "Backup written: {$backupPath}\n";

    if ($apply) {
        $ids = array_map(fn($r) => (int) $r['id'], $toFix);
        $idList = implode(',', $ids);
        $ok = $conn->query("UPDATE pme SET status = 'not_applicable' WHERE id IN ($idList)");
        if ($ok) {
            $conn->commit();
            echo "Applied " . $conn->affected_rows . " updates.\n";
        } else {
            $conn->rollback();
            echo "FAILED: {$conn->error}\n";
        }
    } else {
        $conn->rollback(); // read-only dry run, nothing to keep
        echo "Would apply " . count($toFix) . " updates (re-run with --apply).\n";
    }
} else {
    $conn->rollback(); // nothing to update
}
echo "\n";

echo "==============================================================\n";
echo $apply ? "Done.\n" : "Dry run complete - nothing written. Re-run with --apply to commit.\n";
echo "==============================================================\n";
?>
