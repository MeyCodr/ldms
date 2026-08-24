<?php
/**
 * Apply the corrections HR returned in duplicate_staffno_for_hr_updated.csv.
 *
 *   php apply_hr_staff_updates.php          -> dry run, changes nothing
 *   php apply_hr_staff_updates.php --apply  -> writes, in one transaction
 *
 * The CSV identifies each record by User ID and carries HR's corrected
 * Staff No, Staff Name and Status. Records are matched on id, never on staff
 * number, because the staff number is the thing being corrected.
 *
 * A staff number is only rewritten when the target is FREE. Where HR's number
 * is already held by someone else the row is reported and skipped - for four
 * of them the holder is the same person under their correct number, which
 * makes those merges rather than renames, and merges need a decision about
 * which record survives. Renaming into an occupied number would just create a
 * fresh duplicate.
 *
 * Renaming is otherwise safe: everything in the schema links by user.id, and
 * the only staffno-keyed places (skill_matrix_whitelist, the pme snapshot
 * columns) are checked below.
 */

$apply = in_array('--apply', $argv, true);
$csv = __DIR__ . '/duplicate_staffno_for_hr_updated.csv';

include __DIR__ . '/../dbconn.php';
$conn->query("SET NAMES utf8mb4");

echo $apply ? "=== APPLYING ===\n\n" : "=== DRY RUN - nothing will be written ===\n\n";

$handle = fopen($csv, 'r');
if (!$handle) { fwrite(STDERR, "Cannot open {$csv}\n"); exit(1); }
fgetcsv($handle, 0, ',', '"', '');

$renames = [];
$fields  = [];
$skipped = [];

while (($r = fgetcsv($handle, 0, ',', '"', '')) !== false) {
    if (count($r) < 4) continue;
    $id = (int) trim($r[1]);
    $newNo = trim($r[0]);
    $newName = trim($r[2]);
    $newStatus = trim($r[3]);
    if ($id <= 0 || $newNo === '') continue;

    $stmt = $conn->prepare("SELECT staffno, staffname, status FROM user WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $db = $stmt->get_result()->fetch_assoc();
    if (!$db) { $skipped[] = "id {$id}: no such user"; continue; }

    // ---- staff number ----
    if (trim($db['staffno']) !== $newNo) {
        $stmt = $conn->prepare("SELECT id, staffname FROM user WHERE staffno = ? AND id <> ?");
        $stmt->bind_param('si', $newNo, $id);
        $stmt->execute();
        $holder = $stmt->get_result()->fetch_assoc();
        if ($holder) {
            $skipped[] = sprintf("id %-5s %-34s %s -> %-8s TAKEN by id %s (%s)",
                $id, substr($newName, 0, 33), trim($db['staffno']), $newNo,
                $holder['id'], trim($holder['staffname']));
        } else {
            $renames[] = [$id, trim($db['staffno']), $newNo, $newName];
        }
    }

    // ---- name and status, independent of the number ----
    if (strtoupper(trim($db['staffname'])) !== strtoupper($newName)) {
        $fields[] = [$id, 'staffname', trim($db['staffname']), $newName];
    }
    if (strtoupper(trim((string) $db['status'])) !== strtoupper($newStatus)) {
        $fields[] = [$id, 'status', (string) $db['status'], $newStatus];
    }
}
fclose($handle);

echo "STAFF NUMBER CHANGES (" . count($renames) . "):\n";
foreach ($renames as [$id, $old, $new, $name]) {
    printf("  id %-5s %-34s %-9s -> %s\n", $id, substr($name, 0, 33), $old, $new);
}

echo "\nNAME / STATUS CHANGES (" . count($fields) . "):\n";
foreach ($fields as [$id, $col, $old, $new]) {
    printf("  id %-5s %-10s [%s] -> [%s]\n", $id, $col, $old, $new);
}

if ($skipped) {
    echo "\nSKIPPED - number already taken, needs a merge decision (" . count($skipped) . "):\n";
    foreach ($skipped as $s) echo "  {$s}\n";
}

$conn->begin_transaction();

foreach ($renames as [$id, $old, $new, $name]) {
    $stmt = $conn->prepare("UPDATE user SET staffno = ? WHERE id = ?");
    $stmt->bind_param('si', $new, $id);
    if (!$stmt->execute()) { $conn->rollback(); fwrite(STDERR, "rename failed on id {$id}: {$stmt->error}\n"); exit(1); }
}
foreach ($fields as [$id, $col, $old, $new]) {
    $stmt = $conn->prepare("UPDATE user SET `{$col}` = ? WHERE id = ?");
    $stmt->bind_param('si', $new, $id);
    if (!$stmt->execute()) { $conn->rollback(); fwrite(STDERR, "update failed on id {$id}: {$stmt->error}\n"); exit(1); }
}

$dupes = $conn->query("SELECT COUNT(*) c FROM (SELECT staffno FROM user WHERE staffno<>'' GROUP BY staffno HAVING COUNT(*)>1) d")->fetch_assoc()['c'];
$newDupes = $conn->query("SELECT GROUP_CONCAT(staffno) g FROM (SELECT staffno FROM user WHERE staffno<>'' GROUP BY staffno HAVING COUNT(*)>1) d")->fetch_assoc()['g'];

echo "\nduplicate staff numbers after: {$dupes}\n";
echo "still duplicated: {$newDupes}\n";

if ($apply) {
    $conn->commit();
    echo "\nCOMMITTED.\n";
} else {
    $conn->rollback();
    echo "\nDRY RUN - rolled back, nothing changed. Re-run with --apply to write.\n";
}
?>
