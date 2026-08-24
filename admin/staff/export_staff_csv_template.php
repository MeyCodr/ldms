<?php
    session_start();

    if (!isset($_SESSION['fullname']) || $_SESSION['role'] != 'ADMIN') {
        header("Location: ../../login.php");
        exit();
    }

    /*
     * A starting point only - import_staff_csv.php detects the header row on
     * its own, so these columns may be renamed, reordered or dropped and the
     * file will still import.
     */
    $headers = [
        'Staff No',
        'Staff Name',
        'Email',
        'Gender',
        'Designation',
        'Division',
        'Department',
        'Section',
        'Status',
        'Date Join',
        'Plant',
        'Grade',
        'HOD',
    ];

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="staff_import_template.csv"');
    header('Cache-Control: max-age=0');

    $output = fopen('php://output', 'w');
    // BOM so Excel opens the file as UTF-8 instead of the system codepage.
    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, $headers, ',', '"', '');
    fclose($output);
    exit();
?>
