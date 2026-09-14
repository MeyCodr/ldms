<?php
// One-time backfill: brings every staff member's hodid in line with their
// department's assigned HOD (departments.hod_user_id), the same rule the
// trg_departments_hod_update trigger now applies going forward (see
// scripts/hod_trigger.sql). Needed because that trigger only fires on a
// future change to a department's HOD - it does not retroactively correct
// rows that were already wrong before the trigger was fixed.
//
// Three categories of row this corrects, all in one pass:
//   - self-loop: a department head whose own hodid pointed at themselves
//     (the trigger's old bug, live in production before today's fix)
//   - mismatch: hodid pointed at someone outside the staff member's own
//     department (stale data, e.g. from a department rename/merge)
//   - blank: hodid was 0/NULL despite the department having an assigned HOD
//     (staff added through a path that never set it, e.g. the clerk pages)
//
// A department with no assigned HOD (hod_user_id IS NULL) leaves affected
// staff at hodid = 0 rather than guessing - "no HOD assigned" is preserved,
// not overwritten with an invented value.
//
// Safety:
//   - Dry run by default. Pass --apply to actually write.
//   - Every row this touches is backed up (old and new value) to a TSV
//     before any UPDATE runs, so it can be reversed.
//   - Self-loop guard: a department's own HOD is never set to report to
//     themselves, matching the trigger.

require __DIR__ . '/../dbconn.php';

$apply = in_array('--apply', $argv, true);

$sql = "SELECT u.id, u.staffno, u.staffname, u.department, u.hodid AS old_hodid,
               d.hod_user_id
        FROM user u
        JOIN departments d ON d.id = u.department_id";
$res = $conn->query($sql);

$changes = [];
while ($row = $res->fetch_assoc()) {
    $id = (int) $row['id'];
    $deptHod = $row['hod_user_id'] !== null ? (int) $row['hod_user_id'] : null;

    // Self-loop guard: the department's own HOD does not report to themselves.
    $newHodid = ($deptHod !== null && $deptHod !== $id) ? $deptHod : 0;
    $oldHodid = $row['old_hodid'] !== null ? (int) $row['old_hodid'] : 0;

    if ($newHodid === $oldHodid) {
        continue; // already correct
    }

    $changes[] = [
        'id' => $id,
        'staffno' => $row['staffno'],
        'staffname' => $row['staffname'],
        'department' => $row['department'],
        'old_hodid' => $oldHodid,
        'new_hodid' => $newHodid,
    ];
}

echo "Rows that would change: " . count($changes) . "\n";
if (empty($changes)) {
    echo "Nothing to do.\n";
    exit;
}

$backupPath = __DIR__ . '/backup_hodid_from_departments_20260904_undo_map.tsv';
$fh = fopen($backupPath, 'w');
fwrite($fh, "id\tstaffno\tstaffname\tdepartment\told_hodid\tnew_hodid\n");
foreach ($changes as $c) {
    fwrite($fh, "{$c['id']}\t{$c['staffno']}\t{$c['staffname']}\t{$c['department']}\t{$c['old_hodid']}\t{$c['new_hodid']}\n");
}
fclose($fh);
echo "Backup written: {$backupPath}\n";

if (!$apply) {
    echo "Dry run only - no changes written. Re-run with --apply to commit.\n";
    exit;
}

$stmt = $conn->prepare("UPDATE user SET hodid = ? WHERE id = ?");
$conn->begin_transaction();
try {
    foreach ($changes as $c) {
        $stmt->bind_param('ii', $c['new_hodid'], $c['id']);
        if (!$stmt->execute()) {
            throw new \Exception("Failed on id {$c['id']}: {$conn->error}");
        }
    }
    $conn->commit();
    echo "Applied " . count($changes) . " updates.\n";
} catch (\Throwable $e) {
    $conn->rollback();
    echo "Aborted and rolled back: " . $e->getMessage() . "\n";
    exit(1);
}
?>
