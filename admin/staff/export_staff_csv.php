<?php
    session_start();
    include "../../dbconn.php";

    if (!isset($_SESSION['fullname']) || $_SESSION['role'] != 'ADMIN') {
        header("Location: ../../login.php");
        exit();
    }

    /*
     * Current staff as CSV, using the same header names import_staff.php
     * detects, so this file can be edited and uploaded straight back.
     *
     * "HOD Staff No" is the editable column the importer reads. The HOD name
     * beside it is reference only - its header deliberately does not match
     * any importer synonym, so it is listed as ignored on upload.
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
        'HOD Staff No',
        'Current HOD Name (reference only)',
    ];

    $sql = "SELECT u.staffno, u.staffname, u.email, u.gender, u.designation,
                   u.division, u.department, u.section, u.status, u.date_join,
                   u.plant, u.grade,
                   hod.staffno AS hod_staffno, hod.staffname AS hod_staffname
            FROM user u
            LEFT JOIN user hod ON hod.id = u.hodid
            WHERE u.staffno IS NOT NULL AND u.staffno <> ''
            ORDER BY u.staffno";
    $result = $conn->query($sql);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="staff_' . date('Ymd') . '.csv"');
    header('Cache-Control: max-age=0');

    $output = fopen('php://output', 'w');
    // BOM so Excel opens the file as UTF-8 instead of the system codepage.
    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, $headers, ',', '"', '');

    while ($r = $result->fetch_assoc()) {
        fputcsv($output, [
            $r['staffno'],
            $r['staffname'],
            $r['email'],
            $r['gender'],
            $r['designation'],
            $r['division'],
            $r['department'],
            $r['section'],
            $r['status'],
            $r['date_join'],
            $r['plant'],
            $r['grade'],
            $r['hod_staffno'],
            $r['hod_staffname'],
        ], ',', '"', '');
    }

    fclose($output);
    exit();
?>
