<?php
// One-shot production deploy script combining three fixes that were built
// and verified locally on 2026-09-04/07, but only exist in the LOCAL
// database - a fresh export/import from production wipes them, since
// local is just a copy. This script applies all three directly to
// whatever database dbconn.php points at, so running it on the production
// server (with production's own dbconn.php) fixes it there for good.
//
// The three fixes, in order:
//
//   1. TRIGGER: trg_departments_hod_update on `departments` is patched to
//      guard against a self-loop - without the guard, a department head
//      who is themselves a member of their own department gets their own
//      hodid set to their own id every time their department's HOD is
//      (re)assigned via org.php > Assign HOD. See scripts/hod_trigger.sql
//      for the standalone version of just this trigger.
//
//   2. HODID BACKFILL: brings every staff member's hodid in line with
//      their department's assigned HOD (departments.hod_user_id), the
//      same rule the trigger above applies going forward. Needed because
//      the trigger only fires on a *future* change to a department's HOD -
//      it does not retroactively fix rows that were already wrong.
//      Standalone version: scripts/fix_hodid_from_departments_20260904.php
//
//   3. STATUS BACKFILL: sets status = 'ACTIVE' for CONTRACT staff whose
//      status is blank ('') or NULL - fallout from an undefined-variable
//      bug in the "Add Staff" form (now fixed in code) that silently
//      wrote '' instead of 'ACTIVE'. Standalone version:
//      scripts/fix_blank_status_20260904.php
//
// SAFETY
//   - Dry run by default: reports exactly what each step would change,
//     writes nothing. Pass --apply to actually commit all three steps.
//   - Every row the two backfills touch is written to an undo-map TSV in
//     this directory BEFORE any UPDATE runs, so each step is reversible
//     independently of the others.
//   - The three steps are independent - if step 2 finds nothing to do,
//     step 3 still runs, and vice versa. A hard failure in step 2 or 3
//     rolls back that step's own transaction only.
//
// USAGE
//  - CLI (php scripts/deploy_hod_and_status_fix_20260904.php [--apply]) -
//    no token needed, CLI access already implies server access.
//  - HTTP, for cPanel hosts with no terminal/SSH access - upload this file
//    normally (File Manager, FTP, git deploy, whatever you already use),
//    then visit it in a browser with the matching ?key=:
//      https://<your-domain>/ldms/scripts/deploy_hod_and_status_fix_20260904.php?key=5a37b10decc6cffdb73259e0b00b6f1198060aa0d3d8a4fd
//      https://<your-domain>/ldms/scripts/deploy_hod_and_status_fix_20260904.php?key=...&apply=1
//    Change DEPLOY_FIX_TOKEN below before relying on this - the value here
//    is a placeholder generated for setup, not a secret kept out of source
//    control. Delete this file from the server once you're done with it;
//    it is a one-time fix, not something that needs to stay reachable.
define('DEPLOY_FIX_TOKEN', '5a37b10decc6cffdb73259e0b00b6f1198060aa0d3d8a4fd');

if (PHP_SAPI !== 'cli') {
    if (!isset($_GET['key']) || !hash_equals(DEPLOY_FIX_TOKEN, (string) $_GET['key'])) {
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
echo "HOD + status fix deploy - " . ($apply ? "APPLYING" : "DRY RUN") . "\n";
echo "==============================================================\n\n";

// ===== STEP 1: trigger =====
echo "--- Step 1: trg_departments_hod_update (self-loop guard) ---\n";
$existing = $conn->query("SHOW TRIGGERS WHERE `Table` = 'departments' AND `Trigger` = 'trg_departments_hod_update'");
$hasGuard = false;
if ($existing && $row = $existing->fetch_assoc()) {
    $hasGuard = (strpos($row['Statement'], 'COALESCE(NEW.hod_user_id, 0)') !== false)
             && (strpos($row['Statement'], "id <>") !== false || strpos($row['Statement'], 'id !=') !== false);
}
echo $hasGuard ? "Already has the self-loop guard - nothing to do.\n" : "Missing the self-loop guard.\n";

if (!$hasGuard) {
    if ($apply) {
        $conn->query("DROP TRIGGER IF EXISTS trg_departments_hod_update");
        $ok = $conn->query(
            "CREATE TRIGGER trg_departments_hod_update
             AFTER UPDATE ON departments
             FOR EACH ROW
             BEGIN
                 IF NOT (NEW.hod_user_id <=> OLD.hod_user_id) THEN
                     UPDATE user SET hodid = COALESCE(NEW.hod_user_id, 0)
                     WHERE department_id = NEW.id
                       AND id <> COALESCE(NEW.hod_user_id, 0);
                 END IF;
             END"
        );
        echo $ok ? "Trigger recreated with the guard.\n" : "FAILED to recreate trigger: {$conn->error}\n";
    } else {
        echo "Would recreate it with the guard (re-run with --apply).\n";
    }
}
echo "\n";

// ===== STEP 2: hodid backfill =====
echo "--- Step 2: hodid backfill (department is the source of truth) ---\n";
$res = $conn->query(
    "SELECT u.id, u.staffno, u.staffname, u.department, u.hodid AS old_hodid, d.hod_user_id
     FROM user u JOIN departments d ON d.id = u.department_id"
);
$hodChanges = [];
while ($row = $res->fetch_assoc()) {
    $id = (int) $row['id'];
    $deptHod = $row['hod_user_id'] !== null ? (int) $row['hod_user_id'] : null;
    $newHodid = ($deptHod !== null && $deptHod !== $id) ? $deptHod : 0;
    $oldHodid = $row['old_hodid'] !== null ? (int) $row['old_hodid'] : 0;
    if ($newHodid === $oldHodid) continue;
    $hodChanges[] = ['id' => $id, 'staffno' => $row['staffno'], 'staffname' => $row['staffname'],
                      'department' => $row['department'], 'old_hodid' => $oldHodid, 'new_hodid' => $newHodid];
}
echo "Rows that would change: " . count($hodChanges) . "\n";

if (!empty($hodChanges)) {
    $backupPath = __DIR__ . '/backup_hodid_from_departments_deploy_undo_map.tsv';
    $fh = fopen($backupPath, 'w');
    fwrite($fh, "id\tstaffno\tstaffname\tdepartment\told_hodid\tnew_hodid\n");
    foreach ($hodChanges as $c) {
        fwrite($fh, "{$c['id']}\t{$c['staffno']}\t{$c['staffname']}\t{$c['department']}\t{$c['old_hodid']}\t{$c['new_hodid']}\n");
    }
    fclose($fh);
    echo "Backup written: {$backupPath}\n";

    if ($apply) {
        $stmt = $conn->prepare("UPDATE user SET hodid = ? WHERE id = ?");
        $conn->begin_transaction();
        try {
            foreach ($hodChanges as $c) {
                $stmt->bind_param('ii', $c['new_hodid'], $c['id']);
                if (!$stmt->execute()) throw new \Exception("id {$c['id']}: {$conn->error}");
            }
            $conn->commit();
            echo "Applied " . count($hodChanges) . " hodid updates.\n";
        } catch (\Throwable $e) {
            $conn->rollback();
            echo "Step 2 ABORTED and rolled back: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Would apply " . count($hodChanges) . " updates (re-run with --apply).\n";
    }
}
echo "\n";

// ===== STEP 3: status backfill =====
echo "--- Step 3: blank/NULL CONTRACT status -> ACTIVE ---\n";
$res = $conn->query(
    "SELECT id, staffno, staffname, status FROM user
     WHERE designation = 'CONTRACT' AND (status = '' OR status IS NULL)"
);
$statusChanges = [];
while ($row = $res->fetch_assoc()) {
    $statusChanges[] = $row;
}
echo "Rows that would change: " . count($statusChanges) . "\n";

if (!empty($statusChanges)) {
    $backupPath = __DIR__ . '/backup_blank_status_deploy_undo_map.tsv';
    $fh = fopen($backupPath, 'w');
    fwrite($fh, "id\tstaffno\tstaffname\told_status\tnew_status\n");
    foreach ($statusChanges as $r) {
        $old = $r['status'] === null ? 'NULL' : $r['status'];
        fwrite($fh, "{$r['id']}\t{$r['staffno']}\t{$r['staffname']}\t{$old}\tACTIVE\n");
    }
    fclose($fh);
    echo "Backup written: {$backupPath}\n";

    if ($apply) {
        $stmt = $conn->prepare("UPDATE user SET status = 'ACTIVE' WHERE id = ?");
        $conn->begin_transaction();
        try {
            foreach ($statusChanges as $r) {
                $id = (int) $r['id'];
                $stmt->bind_param('i', $id);
                if (!$stmt->execute()) throw new \Exception("id {$id}: {$conn->error}");
            }
            $conn->commit();
            echo "Applied " . count($statusChanges) . " status updates.\n";
        } catch (\Throwable $e) {
            $conn->rollback();
            echo "Step 3 ABORTED and rolled back: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Would apply " . count($statusChanges) . " updates (re-run with --apply).\n";
    }
}
echo "\n";

echo "==============================================================\n";
echo $apply ? "Done.\n" : "Dry run complete - nothing written. Re-run with --apply to commit.\n";
echo "==============================================================\n";
?>
