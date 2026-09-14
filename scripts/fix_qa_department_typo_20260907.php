<?php
include __DIR__ . "/../dbconn.php";

$id = 159;
$oldDept = 'QUALITY ASSURANCE & CONTROL 1 SA1';
$newDept = 'QUALITY ASSURANCE & CONTROL SA1';

$check = mysqli_query($conn, "SELECT id, staffno, staffname, department FROM user WHERE id = $id");
$row = mysqli_fetch_assoc($check);
if (!$row || $row['department'] !== $oldDept) {
    die("Aborting: record id=$id does not match expected pre-fix state. Found: " . json_encode($row) . "\n");
}

$stmt = $conn->prepare("UPDATE user SET department = ? WHERE id = ? AND department = ?");
$stmt->bind_param("sis", $newDept, $id, $oldDept);
$stmt->execute();

echo "Rows affected: " . $stmt->affected_rows . "\n";

$verify = mysqli_query($conn, "SELECT id, staffno, staffname, division, department, section FROM user WHERE id = $id");
$verifyRow = mysqli_fetch_assoc($verify);
echo "After fix: " . json_encode($verifyRow) . "\n";
?>
