<?php
/**
 * Fill in the missing HOD link from each staff's department.
 *
 *   php fill_hodid_from_department.php          -> dry run, changes nothing
 *   php fill_hodid_from_department.php --apply  -> writes, in one transaction
 *
 * Every department records its HOD in departments.hod_user_id, but 3,558
 * staff still have no hodid of their own - either NULL or the 0 sentinel.
 * This copies the department's HOD down onto them, which is exactly what
 * trg_departments_hod_update already does when a department's HOD is
 * reassigned; this just applies it to records that trigger never fired for.
 *
 * Resigned staff are deliberately skipped. The TNA screens list staff by
 * "hodid != 0", so giving a leaver a HOD would put them back into active
 * TNA lists. Leaving them without one keeps them out, which is correct.
 */

$apply = in_array('--apply', $argv, true);

include __DIR__ . '/../dbconn.php';

echo $apply ? "=== APPLYING ===\n\n" : "=== DRY RUN - nothing will be written ===\n\n";

/* Who is in scope, and what they would get. */
$scope = "FROM user u
          JOIN departments d ON d.id = u.department_id
          LEFT JOIN user h ON h.id = d.hod_user_id
          WHERE (u.hodid IS NULL OR u.hodid = 0)
            AND d.hod_user_id IS NOT NULL
            AND (u.status IS NULL OR u.status <> 'RESIGN')";

$preview = $conn->query("SELECT d.name AS department,
                                h.staffno AS hod_staffno, h.staffname AS hod_name,
                                COUNT(*) AS staff_to_fill
                         {$scope}
                         GROUP BY d.id, h.staffno, h.staffname
                         ORDER BY staff_to_fill DESC, d.name");

printf("%-52s %-10s %-34s %s\n", 'DEPARTMENT', 'HOD', 'HOD NAME', 'STAFF');
echo str_repeat('-', 112), "\n";
$total = 0;
while ($r = $preview->fetch_assoc()) {
    printf("%-52s %-10s %-34s %d\n",
        substr($r['department'], 0, 51),
        $r['hod_staffno'],
        substr($r['hod_name'], 0, 33),
        $r['staff_to_fill']);
    $total += (int) $r['staff_to_fill'];
}
echo str_repeat('-', 112), "\n";
printf("%-99s %d\n\n", 'TOTAL TO FILL', $total);

/* What is deliberately left alone, so the numbers always reconcile. */
$skipped = $conn->query("
    SELECT 'resigned staff (kept out of TNA lists)' AS reason, COUNT(*) n
      FROM user u JOIN departments d ON d.id = u.department_id
     WHERE (u.hodid IS NULL OR u.hodid = 0) AND d.hod_user_id IS NOT NULL AND u.status = 'RESIGN'
    UNION ALL
    SELECT 'no department linked', COUNT(*)
      FROM user u WHERE (u.hodid IS NULL OR u.hodid = 0) AND u.department_id IS NULL
    UNION ALL
    SELECT 'department has no HOD assigned', COUNT(*)
      FROM user u JOIN departments d ON d.id = u.department_id
     WHERE (u.hodid IS NULL OR u.hodid = 0) AND d.hod_user_id IS NULL");
echo "LEFT ALONE:\n";
while ($r = $skipped->fetch_assoc()) {
    printf("  %-60s %d\n", $r['reason'], $r['n']);
}

$before = $conn->query("SELECT COUNT(*) c FROM user WHERE hodid IS NULL OR hodid = 0")->fetch_assoc()['c'];

$conn->begin_transaction();

// Mirrors the trigger: the department's HOD becomes the staff's hodid.
// sync_hod_to_pme fires per row and keeps pme.hodid in step.
$conn->query("UPDATE user u
              JOIN departments d ON d.id = u.department_id
              SET u.hodid = d.hod_user_id
              WHERE (u.hodid IS NULL OR u.hodid = 0)
                AND d.hod_user_id IS NOT NULL
                AND (u.status IS NULL OR u.status <> 'RESIGN')");
$updated = $conn->affected_rows;

$after = $conn->query("SELECT COUNT(*) c FROM user WHERE hodid IS NULL OR hodid = 0")->fetch_assoc()['c'];
$orphans = $conn->query("SELECT COUNT(*) c FROM user u
                         LEFT JOIN user h ON h.id = u.hodid
                         WHERE u.hodid > 0 AND h.id IS NULL")->fetch_assoc()['c'];

echo "\nrows updated        : {$updated}\n";
echo "staff with no HOD   : {$before} -> {$after}\n";
echo "hodid pointing at a missing user: {$orphans}\n";

if ($orphans > 0) {
    $conn->rollback();
    echo "\nABORTED - the fill produced dangling HOD links, rolled back.\n";
    exit(1);
}

if ($apply) {
    $conn->commit();
    echo "\nCOMMITTED.\n";
} else {
    $conn->rollback();
    echo "\nDRY RUN - rolled back, nothing changed. Re-run with --apply to write.\n";
}
?>
