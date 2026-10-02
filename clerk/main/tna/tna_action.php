<?php
include "../../../tna_save.php";

if (isset($_POST['btn_action'])) {
    if ($_POST['btn_action'] == 'addtna') {
        $userid = (string) ($_POST['userid'] ?? '');
        $ok = tna_save_rows($conn, ['userid' => $userid], '1', false, $_POST);
        echo json_encode($ok ? ['message' => 'insert', 'userid' => $userid] : ['message' => 'error']);
    } else if ($_POST['btn_action'] == 'addtnagrade') {
        // userid is posted as "<grade>/<department>" for grade-level TNAs.
        $userid = explode('/', (string) ($_POST['userid'] ?? ''), 2);
        $owner = ['grade' => $userid[0], 'department' => $userid[1] ?? ''];
        $ok = tna_save_rows($conn, $owner, '1', false, $_POST);
        echo json_encode($ok ? ['message' => 'insert', 'userid' => $userid] : ['message' => 'error']);
    }
}
?>
