<?php
include __DIR__ . "/../dbconn.php";

$id = 159;
$currentWrongDept = 'QUALITY ASSURANCE & CONTROL SA1'; // set by the earlier (incorrect) fix
$correctDept = 'QUALITY ASSURANCE & CONTROL 2 (BB/RASA, TM 1 & TM 2)'; // matches department_id=34, whose hod_user_id is this same user

$check = mysqli_query($conn, "SELECT id, staffno, staffname, department, department_id FROM user WHERE id = $id");
$row = mysqli_fetch_assoc($check);
if (!$row || $row['department'] !== $currentWrongDept || (int) $row['department_id'] !== 34) {
    die("Aborting: record id=$id does not match expected pre-fix state. Found: " . json_encode($row) . "\n");
}

$stmt = $conn->prepare("UPDATE user SET department = ? WHERE id = ? AND department = ?");
$stmt->bind_param("sis", $correctDept, $id, $currentWrongDept);
$stmt->execute();

echo "Rows affected: " . $stmt->affected_rows . "\n";

$verify = mysqli_query($conn, "SELECT id, staffno, staffname, division, division_id, department, department_id, section FROM user WHERE id = $id");
$verifyRow = mysqli_fetch_assoc($verify);
echo "After correction: " . json_encode($verifyRow) . "\n";
?>
