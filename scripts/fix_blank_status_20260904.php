<?php
// One-time backfill: sets status = 'ACTIVE' for CONTRACT staff whose status
// is blank ('') or NULL.
//
// Root cause (now fixed): clerk/main/staff/staff_action.php's adduser
// action referenced $status without ever assigning it, so every new
// CONTRACT staff added through the single "Add Staff" form got status =
// '' (PHP silently treats an undefined variable in a string as empty).
// Separately, the Status dropdown itself posted value="" for "ACTIVE" in
// clerk/main and clerk/general's manage_staff.php, so even editing and
// choosing ACTIVE wrote '' instead of the literal string. Both are fixed;
// this script cleans up what already accumulated.
//
// Scoped to CONTRACT only, and only to blank/NULL status - never touches a
// staff member already marked ACTIVE or RESIGN. Checked first: none of the
// blank/NULL rows have a dateresign set, so none of them are a
// mis-recorded resignation - ACTIVE is the correct value for all of them.
//
// Safety:
//   - Dry run by default. Pass --apply to actually write.
//   - Every row this touches is backed up (staffno, staffname, old status)
//     to a TSV before any UPDATE runs, so it can be reversed.

require __DIR__ . '/../dbconn.php';

$apply = in_array('--apply', $argv, true);

$res = $conn->query(
    "SELECT id, staffno, staffname, status FROM user
     WHERE designation = 'CONTRACT' AND (status = '' OR status IS NULL)"
);

$rows = [];
while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
}

echo "Rows that would change: " . count($rows) . "\n";
if (empty($rows)) {
    echo "Nothing to do.\n";
    exit;
}

$backupPath = __DIR__ . '/backup_blank_status_20260904_undo_map.tsv';
$fh = fopen($backupPath, 'w');
fwrite($fh, "id\tstaffno\tstaffname\told_status\tnew_status\n");
foreach ($rows as $r) {
    $old = $r['status'] === null ? 'NULL' : $r['status'];
    fwrite($fh, "{$r['id']}\t{$r['staffno']}\t{$r['staffname']}\t{$old}\tACTIVE\n");
}
fclose($fh);
echo "Backup written: {$backupPath}\n";

if (!$apply) {
    echo "Dry run only - no changes written. Re-run with --apply to commit.\n";
    exit;
}

$stmt = $conn->prepare("UPDATE user SET status = 'ACTIVE' WHERE id = ?");
$conn->begin_transaction();
try {
    foreach ($rows as $r) {
        $id = (int) $r['id'];
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            throw new \Exception("Failed on id {$id}: {$conn->error}");
        }
    }
    $conn->commit();
    echo "Applied " . count($rows) . " updates.\n";
} catch (\Throwable $e) {
    $conn->rollback();
    echo "Aborted and rolled back: " . $e->getMessage() . "\n";
    exit(1);
}
?>
