<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "../../tna_save.php";

if (isset($_POST['btn_action'])) {
    $userid = (string) ($_POST['userid'] ?? '');

    if ($_POST['btn_action'] == 'addtna') {
        $ok = tna_save_rows($conn, ['userid' => $userid], '1', false, $_POST);
        echo json_encode($ok ? ['message' => 'insert', 'userid' => $userid] : ['message' => 'error']);
    } else if ($_POST['btn_action'] == 'approvetna') {
        $ok = tna_save_rows($conn, ['userid' => $userid], 'APPROVE', true, $_POST);
        echo json_encode($ok ? ['message' => 'insertapp'] : ['message' => 'error']);
    }
}
?>
