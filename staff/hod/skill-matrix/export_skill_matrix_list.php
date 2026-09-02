<?php
session_start();
include "../../../dbconn.php";

require '../../../asset/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function canApproveSkillMatrix()
{
    global $conn;

    if (isset($_SESSION['id']) && (!isset($_SESSION['designation']) || !isset($_SESSION['hodid']))) {
        $sessionUserId = (int) $_SESSION['id'];
        $sessionUserQuery = mysqli_query($conn, "SELECT designation, hodid FROM user WHERE id = '$sessionUserId' LIMIT 1");
        if ($sessionUserQuery && $sessionUserRow = mysqli_fetch_assoc($sessionUserQuery)) {
            $_SESSION['designation'] = $sessionUserRow['designation'];
            $_SESSION['hodid'] = $sessionUserRow['hodid'];
        }
    }

    return isset($_SESSION['fullname'], $_SESSION['role'], $_SESSION['designation'], $_SESSION['usertype'], $_SESSION['hodid'])
        && $_SESSION['role'] == ''
        && $_SESSION['designation'] == 'MANAGER (AM/HOS & ABOVE)'
        && (int) $_SESSION['hodid'] != 0
        && $_SESSION['usertype'] == 'HOD';
}

if (!isset($_SESSION['fullname']) || !canApproveSkillMatrix()) {
    header("Location: ../dashboard.php");
    exit();
}

// Mirrors skill-matrix.php's approval-queue query exactly - this page has no
// filter controls, so the export is simply everything currently shown there.
$hodId = (int) $_SESSION['id'];
$currentYear = (int) date('Y');
$currentQuarter = (int) ceil(date('n') / 3);

$stmt = $conn->prepare("SELECT
                            sme.approval_status,
                            target.staffno AS target_staffno,
                            target.staffname AS target_staffname,
                            target.department AS target_department,
                            target.section AS target_section,
                            creator.staffname AS created_by_name
                        FROM skill_matrix_evaluations sme
                        INNER JOIN user target ON target.id = sme.staffid
                        INNER JOIN user creator ON creator.id = sme.created_by
                        WHERE creator.hodid = ?
                        AND (
                            (
                                creator.designation = ?
                                AND (
                                    (creator.roletype = '' AND creator.usertype = '')
                                    OR (creator.roletype = 'CLERK' AND creator.usertype = 'MAIN')
                                )
                            )
                            OR EXISTS (SELECT 1 FROM skill_matrix_whitelist w WHERE w.staffno = creator.staffno COLLATE utf8mb4_0900_ai_ci)
                        )
                        AND sme.approval_status IS NOT NULL
                        AND YEAR(sme.evaluation_date) = ?
                        AND QUARTER(sme.evaluation_date) = ?
                        ORDER BY FIELD(sme.approval_status, 'PENDING', 'APPROVED'), sme.evaluation_date DESC, target.staffname");
$creatorDesignation = "MANAGER (AM/HOS & ABOVE)";
$stmt->bind_param("isii", $hodId, $creatorDesignation, $currentYear, $currentQuarter);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Skill Matrix Approvals');

$headers = ['No.', 'Staff No.', 'Staff Name', 'Department', 'Section', 'Filled By', 'Approval Status'];
$sheet->fromArray($headers, null, 'A1');
$lastCol = 'G';
$sheet->getStyle('A1:' . $lastCol . '1')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
$sheet->getStyle('A1:' . $lastCol . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF337AB7');
$sheet->getStyle('A1:' . $lastCol . '1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$rowNum = 2;
foreach ($rows as $i => $r) {
    $sheet->setCellValueExplicit('A' . $rowNum, $i + 1, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $sheet->setCellValue('B' . $rowNum, $r['target_staffno']);
    $sheet->setCellValue('C' . $rowNum, $r['target_staffname']);
    $sheet->setCellValue('D' . $rowNum, $r['target_department']);
    $sheet->setCellValue('E' . $rowNum, $r['target_section']);
    $sheet->setCellValue('F' . $rowNum, $r['created_by_name']);
    $sheet->setCellValue('G' . $rowNum, $r['approval_status'] == 'APPROVED' ? 'APPROVED' : 'WAITING APPROVAL');
    $rowNum++;
}

foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G'] as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$filename = 'Skill_Matrix_Approvals_Q' . $currentQuarter . '_' . $currentYear . '_' . date('Ymd_His') . '.xlsx';

$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
exit;
