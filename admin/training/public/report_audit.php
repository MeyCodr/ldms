<?php

    include "../../../dbconn.php";

    require '../../../asset/vendor/autoload.php';

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Style\Border;

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getSheet(0);
    $sheet->getSheetView()->setZoomScale(85);

    $query = "select staffno,staffname,gender,designation,department,title,venue,startdate,enddate,starttime,endtime,program,trainer,q1,q2,q3,q4,q5,q6,q7,q8,q9,q10,q11,q12,q13,q14,q15,q16 from training join participation on training.id = trainingid join user on userid = user.id where attendance = 'COMPLETED';";
    $query_run = mysqli_query($conn, $query);

    if(mysqli_num_rows($query_run) > 0)
    {
        $sheet->setCellValue('A1', 'NO');
    $sheet->setCellValue('B1', 'STAFF NUMBER');
    $sheet->setCellValue('C1', 'FULL NAME');
    $sheet->setCellValue('D1', 'GENDER');
    $sheet->setCellValue('E1', 'DESIGNATION');
    $sheet->setCellValue('F1', 'DEPARTMENT');
    $sheet->setCellValue('G1', 'TITLE');
    $sheet->setCellValue('H1', 'VENUE');
    $sheet->setCellValue('I1', 'START DATE');
    $sheet->setCellValue('J1', 'END DATE');
    $sheet->setCellValue('K1', 'START TIME');
    $sheet->setCellValue('L1', 'END TIME');
    $sheet->setCellValue('M1', 'PROGRAM');
    $sheet->setCellValue('N1', 'TRAINER');
    $sheet->setCellValue('O1', 'Knowledge / skill presented in the training is suitable for job application (Pengetahuan/kemahiran yang disampaikan dalam kursus ini sesuai diaplikasikan di  tempat  kerja)');
    $sheet->setCellValue('P1', 'The objective of this course is clearly stated in the beginning of the course (Tujuan kursus ini dinyatakan dengan jelas pada permulaan kursus)');
    $sheet->setCellValue('Q1', 'Training content provided is complete and suitable (Bahan latihan yang digunakan adalah lengkap dan bersesuaian)');
    $sheet->setCellValue('R1', 'Activities carried out such as case study, simulation and group discussion is suitable and effective (Aktiviti yang dijalankan seperti kajian kes, simulasi dan perbincangan kumpulan adalah sesuai dan berkesan)');
    $sheet->setCellValue('S1', 'Training duration is sufficient (Jangkamasa kursus adalah sesuai)');
    $sheet->setCellValue('T1', 'Quality and cleanliness of food preparation throughout the course (Kualiti dan kebersihan makanan yang disediakan sepanjang kursus adalah baik)');
    $sheet->setCellValue('U1', 'Complete training facilities (Kemudahan kursus adalah lengkap)');
    $sheet->setCellValue('V1', 'Overall evaluation (Penilaian secara keseluruhan)');
    $sheet->setCellValue('W1', 'Knowledge on the subject (Pengetahuan berkaitan tajuk kursus)');
    $sheet->setCellValue('X1', 'Methods and manner of presentation (Cara dan gaya penyampaian)');
    $sheet->setCellValue('Y1', 'Overall evaluation (Penilaian secara keseluruhan)');
    $sheet->setCellValue('Z1', 'Would you recommend the course to other employees? (Adakah anda akan mensyorkan kursus ini kepada pekerja lain?)');
    $sheet->setCellValue('AA1', 'What have you benefited from the course? (Apakah faedah-faedah yang telah anda perolehi daripada kursus ini?)');
    $sheet->setCellValue('AB1', 'What is your plan for improvement based on the knowledge gained from this course? (Short term - within 6 months) Apakah perancangan anda untuk pembaikan berdasarkan pengetahuan diperolehi dari kursus ini? (Jangkamasa pendek - dalam tempoh 6 bulan)');
    $sheet->setCellValue('AC1', 'What is your plan for improvement based on the knowledge gained from this course? (Long term - within more than 1 year) Apakah perancangan anda untuk pembaikan berdasarkan pengetahuan diperolehi dari kursus ini? (Jangkamasa panjang - dalam tempoh lebih 1 tahun)');
    $sheet->setCellValue('AD1', 'Please write your comments/suggestions for improvement, problem encountered and other recommendations regarding the course (Sila nyatakan cadangan-cadangan untuk kemajuan, masalah-masalah yang timbul dan lain-lain cadangan berkenaan kursus tersebut)');
        $rowCountOrder = 2;
        $bil = 1;
        foreach($query_run as $data)
        {
			 $sheet->setCellValue('A' . $rowCountOrder, $bil);
        $sheet->setCellValue('B' . $rowCountOrder, $data['staffno']);
        $sheet->setCellValue('C' . $rowCountOrder, $data['staffname']);
        $sheet->setCellValue('D' . $rowCountOrder, $data['gender']);
        $sheet->setCellValue('E' . $rowCountOrder, $data['designation']);
        $sheet->setCellValue('F' . $rowCountOrder, $data['department']);
        $sheet->setCellValue('G' . $rowCountOrder, $data['title']);
        $sheet->setCellValue('H' . $rowCountOrder, $data['venue']);
        $sheet->setCellValue('I' . $rowCountOrder, $data['startdate']);
        $sheet->setCellValue('J' . $rowCountOrder, $data['enddate']);
        $sheet->setCellValue('K' . $rowCountOrder, $data['starttime']);
        $sheet->setCellValue('L' . $rowCountOrder, $data['endtime']);
        $sheet->setCellValue('M' . $rowCountOrder, $data['program']);
        $sheet->setCellValue('N' . $rowCountOrder, $data['trainer']);
        $sheet->setCellValue('O' . $rowCountOrder, $data['q1']);
        $sheet->setCellValue('P' . $rowCountOrder, $data['q2']);
        $sheet->setCellValue('Q' . $rowCountOrder, $data['q3']);
        $sheet->setCellValue('R' . $rowCountOrder, $data['q4']);
        $sheet->setCellValue('S' . $rowCountOrder, $data['q5']);
        $sheet->setCellValue('T' . $rowCountOrder, $data['q6']);
        $sheet->setCellValue('U' . $rowCountOrder, $data['q7']);
        $sheet->setCellValue('V' . $rowCountOrder, $data['q8']);
        $sheet->setCellValue('W' . $rowCountOrder, $data['q9']);
        $sheet->setCellValue('X' . $rowCountOrder, $data['q10']);
        $sheet->setCellValue('Y' . $rowCountOrder, $data['q11']);
        $sheet->setCellValue('Z' . $rowCountOrder, $data['q12']);
        $sheet->setCellValue('AA' . $rowCountOrder, $data['q13']);
        $sheet->setCellValue('AB' . $rowCountOrder, $data['q14']);
        $sheet->setCellValue('AC' . $rowCountOrder, $data['q15']);
        $sheet->setCellValue('AD' . $rowCountOrder, $data['q16']);

            $rowCountOrder++;
            $bil++;
        }
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);
        $sheet->getColumnDimension('L')->setAutoSize(true);
        $sheet->getColumnDimension('M')->setAutoSize(true);
        $sheet->getColumnDimension('N')->setAutoSize(true);
        $sheet->getColumnDimension('O')->setAutoSize(true);
        $sheet->getColumnDimension('P')->setAutoSize(true);
        $sheet->getColumnDimension('Q')->setAutoSize(true);
        $sheet->getColumnDimension('R')->setAutoSize(true);
        $sheet->getColumnDimension('S')->setAutoSize(true);
        $sheet->getColumnDimension('T')->setAutoSize(true);
        $sheet->getColumnDimension('U')->setAutoSize(true);
        $sheet->getColumnDimension('V')->setAutoSize(true);
        $sheet->getColumnDimension('W')->setAutoSize(true);
        $sheet->getColumnDimension('X')->setAutoSize(true);
        $sheet->getColumnDimension('Y')->setAutoSize(true);
        $sheet->getColumnDimension('Z')->setAutoSize(true);
        $sheet->getColumnDimension('AA')->setAutoSize(true);
        $sheet->getColumnDimension('AB')->setAutoSize(true);
        $sheet->getColumnDimension('AC')->setAutoSize(true);
		$sheet->getColumnDimension('AC')->setAutoSize(true);
    }

    $year = date("Y");
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attactment; filename="Audit Report '.$year.'.xlsx"');
    $writer->save('php://output');
?>