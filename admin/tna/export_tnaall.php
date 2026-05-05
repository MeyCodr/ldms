<?php
include "../../dbconn.php";

require '../../asset/vendor/autoload.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

if (isset($_POST['departmentrep'])) {
    $department = $_POST['departmentrep'];
    $rowCountOrder = 2;
    $rowCountTNA = 2;
    $bilstaff = 1;
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getSheet(0);
    $sheet->getSheetView()->setZoomScale(85);

    // $query = "select staffno,staffname,training,othertr,gap,monthapply from user join tna on user.id = userid where user.department = '$department' and designation in ('MANAGER (AM/HOS & ABOVE)','EXECUTIVE') union select staffno,staffname,training,othertr,gap,monthapply from user join tna on user.id = userid where user.department = '$department' and designation = 'NON EXECUTIVE' and usertype != ''";
    // $query_run = mysqli_query($conn, $query);

    // if(mysqli_num_rows($query_run) > 0)
    // {
    //     $rowCountOrder = 2;
    //     $bilstaff = 0;

    //     $firstmerge = 1;
    //     $lastmerge = 1;

    //     $sheet->setCellValue('A1', 'No');
    //     $sheet->getColumnDimension('A')->setAutoSize(true);
    //     $sheet->setCellValue('B1', 'Staff Number');
    //     $sheet->getColumnDimension('B')->setAutoSize(true);
    //     $sheet->setCellValue('C1', 'Staff Name');
    //     $sheet->getColumnDimension('C')->setAutoSize(true);
    //     $sheet->setCellValue('D1', 'Training Title');
    //     $sheet->getColumnDimension('D')->setAutoSize(true);
    //     $sheet->setCellValue('E1', 'Skill Gap');
    //     $sheet->getColumnDimension('E')->setAutoSize(true);
    //     $sheet->setCellValue('F1', 'Target Month');
    //     $sheet->getColumnDimension('F')->setAutoSize(true);

    //     foreach($query_run as $data)
    //     {
    //         $bilstaff++;
    //         $lastname = $sheet->getCell('C'.($rowCountOrder-1))->getValue();
    //         $sheet->setCellValue('A'.$rowCountOrder, $bilstaff);
    //         $sheet->setCellValue('B'.$rowCountOrder, $data['staffno']);
    //         $sheet->setCellValue('C'.$rowCountOrder, $data['staffname']);

    //         if ($data['staffname'] != $lastname) {
    //             $firstmerge = $rowCountOrder;
    //             $lastmerge = $rowCountOrder;
    //         }else {
    //             $lastmerge++;
    //             $bilstaff--;
    //             $sheet->mergeCells('A'.$firstmerge.':A'.$lastmerge);
    //             $sheet->mergeCells('B'.$firstmerge.':B'.$lastmerge);
    //             $sheet->mergeCells('C'.$firstmerge.':C'.$lastmerge);
    //         }

    //         if ($data['training'] != 'OTHERS') {
    //             $sheet->setCellValue('D'.$rowCountOrder, $data['training']);
    //         }else {
    //             $sheet->setCellValue('D'.$rowCountOrder, $data['othertr']);
    //         }

    //         $sheet->setCellValue('E'.$rowCountOrder, $data['gap']);
    //         $sheet->setCellValue('F'.$rowCountOrder, $data['monthapply']);

    //         $rowCountOrder++;
    //     }
    //     $sheet->getStyle('A1:F'.($rowCountOrder-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // }
    $query = "select userid,staffno,staffname from user join tna on user.id = userid where user.department = '$department' and designation in ('MANAGER (AM/HOS & ABOVE)','EXECUTIVE') union select userid,staffno,staffname from user join tna on user.id = userid where user.department = '$department' and designation = 'NON EXECUTIVE' and usertype != ''";
    $query_run = mysqli_query($conn, $query);

    if (mysqli_num_rows($query_run) > 0) {


        $sheet->setCellValue('A1', 'No');
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->setCellValue('B1', 'Staff Number');
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->setCellValue('C1', 'Staff Name');
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->setCellValue('D1', 'Training Title');
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->setCellValue('E1', 'Skill Gap');
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->setCellValue('F1', 'Target Month');
        $sheet->getColumnDimension('F')->setAutoSize(true);

        foreach ($query_run as $data) {
            $sheet->setCellValue('A' . $rowCountOrder, $bilstaff);
            $sheet->setCellValue('B' . $rowCountOrder, $data['staffno']);
            $sheet->setCellValue('C' . $rowCountOrder, $data['staffname']);
            $userid = $data['userid'];
            $query1 = "select training,othertr,gap,monthapply from tna where userid = '$userid'";
            $query_run1 = mysqli_query($conn, $query1);

            if (mysqli_num_rows($query_run1) > 0) {
                foreach ($query_run1 as $data1) {
                    if ($data1['training'] != 'OTHERS') {
                        $sheet->setCellValue('D' . $rowCountTNA, $data1['training']);
                    } else {
                        $sheet->setCellValue('D' . $rowCountTNA, $data1['othertr']);
                    }
                    $sheet->setCellValue('E' . $rowCountTNA, $data1['gap']);
                    $sheet->setCellValue('F' . $rowCountTNA, $data1['monthapply']);
                    $rowCountTNA++;
                }
            }
            $sheet->mergeCells('A' . $rowCountOrder . ':A' . ($rowCountTNA - 1));
            $sheet->mergeCells('B' . $rowCountOrder . ':B' . ($rowCountTNA - 1));
            $sheet->mergeCells('C' . $rowCountOrder . ':C' . ($rowCountTNA - 1));
            $rowCountOrder = $rowCountTNA;
            $bilstaff++;
        }
    }

    $query = "select distinct(grade) from tna where department = '$department'";
    $query_run = mysqli_query($conn, $query);

    if (mysqli_num_rows($query_run) > 0) {
        foreach ($query_run as $data) {
            $sheet->setCellValue('A' . $rowCountOrder, $bilstaff);
            $sheet->setCellValue('B' . $rowCountOrder, '');
            $sheet->setCellValue('C' . $rowCountOrder, $data['grade']);
            $grade = $data['grade'];
            $query1 = "select training,othertr,gap,monthapply from tna where department = '$department' and grade = '$grade'";
            $query_run1 = mysqli_query($conn, $query1);

            if (mysqli_num_rows($query_run1) > 0) {
                foreach ($query_run1 as $data1) {
                    if ($data1['training'] != 'OTHERS') {
                        $sheet->setCellValue('D' . $rowCountTNA, $data1['training']);
                    } else {
                        $sheet->setCellValue('D' . $rowCountTNA, $data1['othertr']);
                    }
                    $sheet->setCellValue('E' . $rowCountTNA, $data1['gap']);
                    $sheet->setCellValue('F' . $rowCountTNA, $data1['monthapply']);
                    $rowCountTNA++;
                }
            }
            $sheet->mergeCells('A' . $rowCountOrder . ':A' . ($rowCountTNA - 1));
            $sheet->mergeCells('B' . $rowCountOrder . ':B' . ($rowCountTNA - 1));
            $sheet->mergeCells('C' . $rowCountOrder . ':C' . ($rowCountTNA - 1));
            $rowCountOrder = $rowCountTNA;
            $bilstaff++;
        }
    }

    $sheet->getStyle('A1:F' . ($rowCountOrder - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Training Hours.xlsx"');
    $writer->save('php://output');
}
?>