<?php
include "../../../tna_save.php";

if (isset($_POST['btn_action'])) {
    if ($_POST['btn_action'] == 'addtna') {
        $userid = (string) ($_POST['userid'] ?? '');

        $stmt = $conn->prepare("SELECT department FROM user WHERE id = ?");
        $stmt->bind_param('s', $userid);
        $stmt->execute();
        $rowDept = $stmt->get_result()->fetch_assoc();
        $department = $rowDept['department'] ?? '';

        // Only the ESG rows have ever carried the department here; the
        // individual TNA summary (export_tnaall1.php) picks staff rows out
        // with department = '', so this is kept exactly as it was.
        $extra = ['esgaware' => ['department' => $department]];
        $ok = tna_save_rows($conn, ['userid' => $userid], '1', false, $_POST, $extra);
        echo json_encode($ok ? ['message' => 'insert', 'userid' => $userid] : ['message' => 'error']);
    }
}
?>
