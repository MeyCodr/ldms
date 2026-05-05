<?php

    include "../../dbconn.php";

    require '../../asset/vendor/autoload.php';

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Style\Border;

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getSheet(0);
    $sheet->getSheetView()->setZoomScale(85);

    $query = "select tablea.department,ifnull(totalemp,0) as totalman,ifnull(totalhours,0) as totalhour,ifnull(round(totalhours/totalemp,2),0) as avghours from (select tablestaff.department,ifnull(totalstaff+totalcontract,totalstaff) as totalemp from (select department,count(*) as totalstaff from user group by department)tablestaff left join (select department,max(participateojt.totalman) as totalcontract from ojt join participateojt on ojt.id = ojtid where userid = 0 and month(startdate) = 1 group by department)tablecontract on tablestaff.department = tablecontract.department)tablea left join (select department, sum(lasttotal) as totalhours from (select department,totaldays * totalhours * totalman as lasttotal from (select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,count(*) as totalman,department from training join participation on training.id = trainingid join user on userid = user.id where month(startdate) = 1 group by department union select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,ojt.totalman,department from ojt join participateojt on ojt.id = ojtid where month(startdate) = 1 group by department)tablea)tablehours group by department)tableb on tablea.department = tableb.department;";
    $query_run = mysqli_query($conn, $query);

    if(mysqli_num_rows($query_run) > 0)
    {
        $sheet->mergeCells('A1:A2')->setCellValue('A1', 'NO');
        $sheet->mergeCells('B1:B2')->setCellValue('B1', 'DEPARTMENT');
        $sheet->mergeCells('C1:E1')->setCellValue('C1', 'JANUARY');
        $sheet->setCellValue('C2', 'MAN POWER');
        $sheet->setCellValue('D2', 'TOTAL TR HRS');
		$sheet->setCellValue('E2', 'AVG TR HRS');
        $rowCountOrder = 3;
        $bil = 1;
        foreach($query_run as $data)
        {
			$sheet->setCellValue('A'.$rowCountOrder, $bil);
            $sheet->setCellValue('B'.$rowCountOrder, $data['department']);
            $sheet->setCellValue('C'.$rowCountOrder, $data['totalman']);
            $sheet->setCellValue('D'.$rowCountOrder, $data['totalhour']);
            $sheet->setCellValue('E'.$rowCountOrder, $data['avghours']);

            if ($data['avghours'] < 4) {
                $sheet->getStyle('E'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF0000');
            }else if ($data['avghours'] >= 4) {
                $sheet->getStyle('E'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92D050');
            }

            $rowCountOrder++;
            $bil++;
        }
        $sheet->setCellValue('B'.($rowCountOrder+1), 'Avg TR Hours');
        $sheet->setCellValue('C'.$rowCountOrder, '=SUM(C3:C'.($rowCountOrder-1).')');
        $sheet->setCellValue('D'.$rowCountOrder, '=SUM(D3:D'.($rowCountOrder-1).')');
        $sheet->setCellValue('D'.($rowCountOrder+1), '=D'.($rowCountOrder).'/C'.($rowCountOrder));

        $sheet->getStyle('D'.($rowCountOrder+1))->getNumberFormat()->setFormatCode('0.00'); 
        $sheet->getColumnDimension('A')->setAutoSize(false);
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(false);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getStyle('D'.($rowCountOrder))->getFont()->setBold( true );
        $sheet->getStyle('D'.($rowCountOrder+1))->getFont()->setBold( true );
        $sheet->getStyle('B'.($rowCountOrder+1))->getFont()->setBold( true );
        $sheet->getStyle('A1:E'.($rowCountOrder-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    $query = "select tablea.department,ifnull(totalemp,0) as totalman,ifnull(totalhours,0) as totalhour,ifnull(round(totalhours/totalemp,2),0) as avghours from (select tablestaff.department,ifnull(totalstaff+totalcontract,totalstaff) as totalemp from (select department,count(*) as totalstaff from user group by department)tablestaff left join (select department,max(participateojt.totalman) as totalcontract from ojt join participateojt on ojt.id = ojtid where userid = 0 and month(startdate) = 2 group by department)tablecontract on tablestaff.department = tablecontract.department)tablea left join (select department, sum(lasttotal) as totalhours from (select department,totaldays * totalhours * totalman as lasttotal from (select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,count(*) as totalman,department from training join participation on training.id = trainingid join user on userid = user.id where month(startdate) = 2 group by department union select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,ojt.totalman,department from ojt join participateojt on ojt.id = ojtid where month(startdate) = 2 group by department)tablea)tablehours group by department)tableb on tablea.department = tableb.department;";
    $query_run = mysqli_query($conn, $query);

    if(mysqli_num_rows($query_run) > 0)
    {
        $sheet->mergeCells('F1:H1')->setCellValue('F1', 'FEBRUARY');
        $sheet->setCellValue('F2', 'MAN POWER');
        $sheet->setCellValue('G2', 'TOTAL TR HRS');
		$sheet->setCellValue('H2', 'AVG TR HRS');
        $rowCountOrder = 3;
        foreach($query_run as $data)
        {
            $sheet->setCellValue('F'.$rowCountOrder, $data['totalman']);
            $sheet->setCellValue('G'.$rowCountOrder, $data['totalhour']);
            $sheet->setCellValue('H'.$rowCountOrder, $data['avghours']);

            if ($data['avghours'] < 4) {
                $sheet->getStyle('H'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF0000');
            }else if ($data['avghours'] >= 4) {
                $sheet->getStyle('H'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92D050');
            }

            $rowCountOrder++;
        }
        $sheet->setCellValue('F'.$rowCountOrder, '=SUM(F3:F'.($rowCountOrder-1).')');
        $sheet->setCellValue('G'.$rowCountOrder, '=SUM(G3:G'.($rowCountOrder-1).')');
        $sheet->setCellValue('G'.($rowCountOrder+1), '=G'.($rowCountOrder).'/F'.($rowCountOrder));

        $sheet->getStyle('G'.($rowCountOrder+1))->getNumberFormat()->setFormatCode('0.00'); 
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getStyle('G'.($rowCountOrder))->getFont()->setBold( true );
        $sheet->getStyle('G'.($rowCountOrder+1))->getFont()->setBold( true );
        $sheet->getStyle('F1:H'.($rowCountOrder-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    $query = "select tablea.department,ifnull(totalemp,0) as totalman,ifnull(totalhours,0) as totalhour,ifnull(round(totalhours/totalemp,2),0) as avghours from (select tablestaff.department,ifnull(totalstaff+totalcontract,totalstaff) as totalemp from (select department,count(*) as totalstaff from user group by department)tablestaff left join (select department,max(participateojt.totalman) as totalcontract from ojt join participateojt on ojt.id = ojtid where userid = 0 and month(startdate) = 3 group by department)tablecontract on tablestaff.department = tablecontract.department)tablea left join (select department, sum(lasttotal) as totalhours from (select department,totaldays * totalhours * totalman as lasttotal from (select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,count(*) as totalman,department from training join participation on training.id = trainingid join user on userid = user.id where month(startdate) = 3 group by department union select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,ojt.totalman,department from ojt join participateojt on ojt.id = ojtid where month(startdate) = 3 group by department)tablea)tablehours group by department)tableb on tablea.department = tableb.department;";
    $query_run = mysqli_query($conn, $query);

    if(mysqli_num_rows($query_run) > 0)
    {
        $sheet->mergeCells('I1:K1')->setCellValue('I1', 'MARCH');
        $sheet->setCellValue('I2', 'MAN POWER');
        $sheet->setCellValue('J2', 'TOTAL TR HRS');
		$sheet->setCellValue('K2', 'AVG TR HRS');
        $rowCountOrder = 3;
        foreach($query_run as $data)
        {
            $sheet->setCellValue('I'.$rowCountOrder, $data['totalman']);
            $sheet->setCellValue('J'.$rowCountOrder, $data['totalhour']);
            $sheet->setCellValue('K'.$rowCountOrder, $data['avghours']);

            if ($data['avghours'] < 4) {
                $sheet->getStyle('K'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF0000');
            }else if ($data['avghours'] >= 4) {
                $sheet->getStyle('K'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92D050');
            }

            $rowCountOrder++;
        }
        $sheet->setCellValue('I'.$rowCountOrder, '=SUM(I3:I'.($rowCountOrder-1).')');
        $sheet->setCellValue('J'.$rowCountOrder, '=SUM(J3:J'.($rowCountOrder-1).')');
        $sheet->setCellValue('J'.($rowCountOrder+1), '=J'.($rowCountOrder).'/I'.($rowCountOrder));

        $sheet->getStyle('J'.($rowCountOrder+1))->getNumberFormat()->setFormatCode('0.00'); 
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);
        $sheet->getStyle('J'.($rowCountOrder))->getFont()->setBold( true );
        $sheet->getStyle('J'.($rowCountOrder+1))->getFont()->setBold( true );
        $sheet->getStyle('I1:K'.($rowCountOrder-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    $query = "select tablea.department,ifnull(totalemp,0) as totalman,ifnull(totalhours,0) as totalhour,ifnull(round(totalhours/totalemp,2),0) as avghours from (select tablestaff.department,ifnull(totalstaff+totalcontract,totalstaff) as totalemp from (select department,count(*) as totalstaff from user group by department)tablestaff left join (select department,max(participateojt.totalman) as totalcontract from ojt join participateojt on ojt.id = ojtid where userid = 0 and month(startdate) = 4 group by department)tablecontract on tablestaff.department = tablecontract.department)tablea left join (select department, sum(lasttotal) as totalhours from (select department,totaldays * totalhours * totalman as lasttotal from (select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,count(*) as totalman,department from training join participation on training.id = trainingid join user on userid = user.id where month(startdate) = 4 group by department union select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,ojt.totalman,department from ojt join participateojt on ojt.id = ojtid where month(startdate) = 4 group by department)tablea)tablehours group by department)tableb on tablea.department = tableb.department;";
    $query_run = mysqli_query($conn, $query);

    if(mysqli_num_rows($query_run) > 0)
    {
        $sheet->mergeCells('L1:N1')->setCellValue('L1', 'APRIL');
        $sheet->setCellValue('L2', 'MAN POWER');
        $sheet->setCellValue('M2', 'TOTAL TR HRS');
		$sheet->setCellValue('N2', 'AVG TR HRS');
        $rowCountOrder = 3;
        foreach($query_run as $data)
        {
            $sheet->setCellValue('L'.$rowCountOrder, $data['totalman']);
            $sheet->setCellValue('M'.$rowCountOrder, $data['totalhour']);
            $sheet->setCellValue('N'.$rowCountOrder, $data['avghours']);

            if ($data['avghours'] < 4) {
                $sheet->getStyle('N'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF0000');
            }else if ($data['avghours'] >= 4) {
                $sheet->getStyle('N'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92D050');
            }

            $rowCountOrder++;
        }
        $sheet->setCellValue('L'.$rowCountOrder, '=SUM(L3:L'.($rowCountOrder-1).')');
        $sheet->setCellValue('M'.$rowCountOrder, '=SUM(M3:M'.($rowCountOrder-1).')');
        $sheet->setCellValue('M'.($rowCountOrder+1), '=M'.($rowCountOrder).'/L'.($rowCountOrder));

        $sheet->getStyle('M'.($rowCountOrder+1))->getNumberFormat()->setFormatCode('0.00'); 
        $sheet->getColumnDimension('L')->setAutoSize(true);
        $sheet->getColumnDimension('M')->setAutoSize(true);
        $sheet->getColumnDimension('N')->setAutoSize(true);
        $sheet->getStyle('M'.($rowCountOrder))->getFont()->setBold( true );
        $sheet->getStyle('M'.($rowCountOrder+1))->getFont()->setBold( true );
        $sheet->getStyle('L1:N'.($rowCountOrder-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    $query = "select tablea.department,ifnull(totalemp,0) as totalman,ifnull(totalhours,0) as totalhour,ifnull(round(totalhours/totalemp,2),0) as avghours from (select tablestaff.department,ifnull(totalstaff+totalcontract,totalstaff) as totalemp from (select department,count(*) as totalstaff from user group by department)tablestaff left join (select department,max(participateojt.totalman) as totalcontract from ojt join participateojt on ojt.id = ojtid where userid = 0 and month(startdate) = 5 group by department)tablecontract on tablestaff.department = tablecontract.department)tablea left join (select department, sum(lasttotal) as totalhours from (select department,totaldays * totalhours * totalman as lasttotal from (select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,count(*) as totalman,department from training join participation on training.id = trainingid join user on userid = user.id where month(startdate) = 5 group by department union select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,ojt.totalman,department from ojt join participateojt on ojt.id = ojtid where month(startdate) = 5 group by department)tablea)tablehours group by department)tableb on tablea.department = tableb.department;";
    $query_run = mysqli_query($conn, $query);

    if(mysqli_num_rows($query_run) > 0)
    {
        $sheet->mergeCells('O1:Q1')->setCellValue('O1', 'MAY');
        $sheet->setCellValue('O2', 'MAN POWER');
        $sheet->setCellValue('P2', 'TOTAL TR HRS');
		$sheet->setCellValue('Q2', 'AVG TR HRS');
        $rowCountOrder = 3;
        foreach($query_run as $data)
        {
            $sheet->setCellValue('O'.$rowCountOrder, $data['totalman']);
            $sheet->setCellValue('P'.$rowCountOrder, $data['totalhour']);
            $sheet->setCellValue('Q'.$rowCountOrder, $data['avghours']);

            if ($data['avghours'] < 4) {
                $sheet->getStyle('Q'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF0000');
            }else if ($data['avghours'] >= 4) {
                $sheet->getStyle('Q'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92D050');
            }

            $rowCountOrder++;
        }
        $sheet->setCellValue('O'.$rowCountOrder, '=SUM(O3:O'.($rowCountOrder-1).')');
        $sheet->setCellValue('P'.$rowCountOrder, '=SUM(P3:P'.($rowCountOrder-1).')');
        $sheet->setCellValue('P'.($rowCountOrder+1), '=P'.($rowCountOrder).'/O'.($rowCountOrder));

        $sheet->getStyle('P'.($rowCountOrder+1))->getNumberFormat()->setFormatCode('0.00'); 
        $sheet->getColumnDimension('O')->setAutoSize(true);
        $sheet->getColumnDimension('P')->setAutoSize(true);
        $sheet->getColumnDimension('Q')->setAutoSize(true);
        $sheet->getStyle('P'.($rowCountOrder))->getFont()->setBold( true );
        $sheet->getStyle('P'.($rowCountOrder+1))->getFont()->setBold( true );
        $sheet->getStyle('O1:Q'.($rowCountOrder-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    $query = "select tablea.department,ifnull(totalemp,0) as totalman,ifnull(totalhours,0) as totalhour,ifnull(round(totalhours/totalemp,2),0) as avghours from (select tablestaff.department,ifnull(totalstaff+totalcontract,totalstaff) as totalemp from (select department,count(*) as totalstaff from user group by department)tablestaff left join (select department,max(participateojt.totalman) as totalcontract from ojt join participateojt on ojt.id = ojtid where userid = 0 and month(startdate) = 6 group by department)tablecontract on tablestaff.department = tablecontract.department)tablea left join (select department, sum(lasttotal) as totalhours from (select department,totaldays * totalhours * totalman as lasttotal from (select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,count(*) as totalman,department from training join participation on training.id = trainingid join user on userid = user.id where month(startdate) = 6 group by department union select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,ojt.totalman,department from ojt join participateojt on ojt.id = ojtid where month(startdate) = 6 group by department)tablea)tablehours group by department)tableb on tablea.department = tableb.department;";
    $query_run = mysqli_query($conn, $query);

    if(mysqli_num_rows($query_run) > 0)
    {
        $sheet->mergeCells('R1:T1')->setCellValue('R1', 'JUNE');
        $sheet->setCellValue('R2', 'MAN POWER');
        $sheet->setCellValue('S2', 'TOTAL TR HRS');
		$sheet->setCellValue('T2', 'AVG TR HRS');
        $rowCountOrder = 3;
        foreach($query_run as $data)
        {
            $sheet->setCellValue('R'.$rowCountOrder, $data['totalman']);
            $sheet->setCellValue('S'.$rowCountOrder, $data['totalhour']);
            $sheet->setCellValue('T'.$rowCountOrder, $data['avghours']);

            if ($data['avghours'] < 4) {
                $sheet->getStyle('T'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF0000');
            }else if ($data['avghours'] >= 4) {
                $sheet->getStyle('T'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92D050');
            }

            $rowCountOrder++;
        }
        $sheet->setCellValue('R'.$rowCountOrder, '=SUM(R3:R'.($rowCountOrder-1).')');
        $sheet->setCellValue('S'.$rowCountOrder, '=SUM(S3:S'.($rowCountOrder-1).')');
        $sheet->setCellValue('S'.($rowCountOrder+1), '=S'.($rowCountOrder).'/R'.($rowCountOrder));

        $sheet->getStyle('S'.($rowCountOrder+1))->getNumberFormat()->setFormatCode('0.00'); 
        $sheet->getColumnDimension('R')->setAutoSize(true);
        $sheet->getColumnDimension('S')->setAutoSize(true);
        $sheet->getColumnDimension('T')->setAutoSize(true);
        $sheet->getStyle('S'.($rowCountOrder))->getFont()->setBold( true );
        $sheet->getStyle('S'.($rowCountOrder+1))->getFont()->setBold( true );
        $sheet->getStyle('R1:T'.($rowCountOrder-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    $query = "select tablea.department,ifnull(totalemp,0) as totalman,ifnull(totalhours,0) as totalhour,ifnull(round(totalhours/totalemp,2),0) as avghours from (select tablestaff.department,ifnull(totalstaff+totalcontract,totalstaff) as totalemp from (select department,count(*) as totalstaff from user group by department)tablestaff left join (select department,max(participateojt.totalman) as totalcontract from ojt join participateojt on ojt.id = ojtid where userid = 0 and month(startdate) = 7 group by department)tablecontract on tablestaff.department = tablecontract.department)tablea left join (select department, sum(lasttotal) as totalhours from (select department,totaldays * totalhours * totalman as lasttotal from (select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,count(*) as totalman,department from training join participation on training.id = trainingid join user on userid = user.id where month(startdate) = 7 group by department union select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,ojt.totalman,department from ojt join participateojt on ojt.id = ojtid where month(startdate) = 7 group by department)tablea)tablehours group by department)tableb on tablea.department = tableb.department;";
    $query_run = mysqli_query($conn, $query);

    if(mysqli_num_rows($query_run) > 0)
    {
        $sheet->mergeCells('U1:W1')->setCellValue('U1', 'JULY');
        $sheet->setCellValue('U2', 'MAN POWER');
        $sheet->setCellValue('V2', 'TOTAL TR HRS');
		$sheet->setCellValue('W2', 'AVG TR HRS');
        $rowCountOrder = 3;
        foreach($query_run as $data)
        {
            $sheet->setCellValue('U'.$rowCountOrder, $data['totalman']);
            $sheet->setCellValue('V'.$rowCountOrder, $data['totalhour']);
            $sheet->setCellValue('W'.$rowCountOrder, $data['avghours']);

            if ($data['avghours'] < 4) {
                $sheet->getStyle('W'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF0000');
            }else if ($data['avghours'] >= 4) {
                $sheet->getStyle('W'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92D050');
            }

            $rowCountOrder++;
        }
        $sheet->setCellValue('U'.$rowCountOrder, '=SUM(U3:U'.($rowCountOrder-1).')');
        $sheet->setCellValue('V'.$rowCountOrder, '=SUM(V3:V'.($rowCountOrder-1).')');
        $sheet->setCellValue('V'.($rowCountOrder+1), '=V'.($rowCountOrder).'/U'.($rowCountOrder));

        $sheet->getStyle('V'.($rowCountOrder+1))->getNumberFormat()->setFormatCode('0.00'); 
        $sheet->getColumnDimension('U')->setAutoSize(true);
        $sheet->getColumnDimension('V')->setAutoSize(true);
        $sheet->getColumnDimension('W')->setAutoSize(true);
        $sheet->getStyle('V'.($rowCountOrder))->getFont()->setBold( true );
        $sheet->getStyle('V'.($rowCountOrder+1))->getFont()->setBold( true );
        $sheet->getStyle('U1:W'.($rowCountOrder-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    $query = "select tablea.department,ifnull(totalemp,0) as totalman,ifnull(totalhours,0) as totalhour,ifnull(round(totalhours/totalemp,2),0) as avghours from (select tablestaff.department,ifnull(totalstaff+totalcontract,totalstaff) as totalemp from (select department,count(*) as totalstaff from user group by department)tablestaff left join (select department,max(participateojt.totalman) as totalcontract from ojt join participateojt on ojt.id = ojtid where userid = 0 and month(startdate) = 8 group by department)tablecontract on tablestaff.department = tablecontract.department)tablea left join (select department, sum(lasttotal) as totalhours from (select department,totaldays * totalhours * totalman as lasttotal from (select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,count(*) as totalman,department from training join participation on training.id = trainingid join user on userid = user.id where month(startdate) = 8 group by department union select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,ojt.totalman,department from ojt join participateojt on ojt.id = ojtid where month(startdate) = 8 group by department)tablea)tablehours group by department)tableb on tablea.department = tableb.department;";
    $query_run = mysqli_query($conn, $query);

    if(mysqli_num_rows($query_run) > 0)
    {
        $sheet->mergeCells('X1:Z1')->setCellValue('X1', 'AUGUST');
        $sheet->setCellValue('X2', 'MAN POWER');
        $sheet->setCellValue('Y2', 'TOTAL TR HRS');
		$sheet->setCellValue('Z2', 'AVG TR HRS');
        $rowCountOrder = 3;
        foreach($query_run as $data)
        {
            $sheet->setCellValue('X'.$rowCountOrder, $data['totalman']);
            $sheet->setCellValue('Y'.$rowCountOrder, $data['totalhour']);
            $sheet->setCellValue('Z'.$rowCountOrder, $data['avghours']);

            if ($data['avghours'] < 4) {
                $sheet->getStyle('Z'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF0000');
            }else if ($data['avghours'] >= 4) {
                $sheet->getStyle('Z'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92D050');
            }

            $rowCountOrder++;
        }
        $sheet->setCellValue('X'.$rowCountOrder, '=SUM(X3:X'.($rowCountOrder-1).')');
        $sheet->setCellValue('Y'.$rowCountOrder, '=SUM(Y3:Y'.($rowCountOrder-1).')');
        $sheet->setCellValue('Y'.($rowCountOrder+1), '=Y'.($rowCountOrder).'/X'.($rowCountOrder));

        $sheet->getStyle('Y'.($rowCountOrder+1))->getNumberFormat()->setFormatCode('0.00'); 
        $sheet->getColumnDimension('X')->setAutoSize(true);
        $sheet->getColumnDimension('Y')->setAutoSize(true);
        $sheet->getColumnDimension('Z')->setAutoSize(true);
        $sheet->getStyle('Y'.($rowCountOrder))->getFont()->setBold( true );
        $sheet->getStyle('Y'.($rowCountOrder+1))->getFont()->setBold( true );
        $sheet->getStyle('X1:Z'.($rowCountOrder-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    $query = "select tablea.department,ifnull(totalemp,0) as totalman,ifnull(totalhours,0) as totalhour,ifnull(round(totalhours/totalemp,2),0) as avghours from (select tablestaff.department,ifnull(totalstaff+totalcontract,totalstaff) as totalemp from (select department,count(*) as totalstaff from user group by department)tablestaff left join (select department,max(participateojt.totalman) as totalcontract from ojt join participateojt on ojt.id = ojtid where userid = 0 and month(startdate) = 9 group by department)tablecontract on tablestaff.department = tablecontract.department)tablea left join (select department, sum(lasttotal) as totalhours from (select department,totaldays * totalhours * totalman as lasttotal from (select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,count(*) as totalman,department from training join participation on training.id = trainingid join user on userid = user.id where month(startdate) = 9 group by department union select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,ojt.totalman,department from ojt join participateojt on ojt.id = ojtid where month(startdate) = 9 group by department)tablea)tablehours group by department)tableb on tablea.department = tableb.department;";
    $query_run = mysqli_query($conn, $query);

    if(mysqli_num_rows($query_run) > 0)
    {
        $sheet->mergeCells('AA1:AC1')->setCellValue('AA1', 'SEPTEMBER');
        $sheet->setCellValue('AA2', 'MAN POWER');
        $sheet->setCellValue('AB2', 'TOTAL TR HRS');
		$sheet->setCellValue('AC2', 'AVG TR HRS');
        $rowCountOrder = 3;
        foreach($query_run as $data)
        {
            $sheet->setCellValue('AA'.$rowCountOrder, $data['totalman']);
            $sheet->setCellValue('AB'.$rowCountOrder, $data['totalhour']);
            $sheet->setCellValue('AC'.$rowCountOrder, $data['avghours']);

            if ($data['avghours'] < 4) {
                $sheet->getStyle('AC'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF0000');
            }else if ($data['avghours'] >= 4) {
                $sheet->getStyle('AC'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92D050');
            }

            $rowCountOrder++;
        }
        $sheet->setCellValue('AA'.$rowCountOrder, '=SUM(AA3:AA'.($rowCountOrder-1).')');
        $sheet->setCellValue('AB'.$rowCountOrder, '=SUM(AB3:AB'.($rowCountOrder-1).')');
        $sheet->setCellValue('AB'.($rowCountOrder+1), '=AB'.($rowCountOrder).'/AA'.($rowCountOrder));

        $sheet->getStyle('AB'.($rowCountOrder+1))->getNumberFormat()->setFormatCode('0.00'); 
        $sheet->getColumnDimension('AA')->setAutoSize(true);
        $sheet->getColumnDimension('AB')->setAutoSize(true);
        $sheet->getColumnDimension('AC')->setAutoSize(true);
        $sheet->getStyle('AB'.($rowCountOrder))->getFont()->setBold( true );
        $sheet->getStyle('AB'.($rowCountOrder+1))->getFont()->setBold( true );
        $sheet->getStyle('AA1:AC'.($rowCountOrder-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    $query = "select tablea.department,ifnull(totalemp,0) as totalman,ifnull(totalhours,0) as totalhour,ifnull(round(totalhours/totalemp,2),0) as avghours from (select tablestaff.department,ifnull(totalstaff+totalcontract,totalstaff) as totalemp from (select department,count(*) as totalstaff from user group by department)tablestaff left join (select department,max(participateojt.totalman) as totalcontract from ojt join participateojt on ojt.id = ojtid where userid = 0 and month(startdate) = 10 group by department)tablecontract on tablestaff.department = tablecontract.department)tablea left join (select department, sum(lasttotal) as totalhours from (select department,totaldays * totalhours * totalman as lasttotal from (select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,count(*) as totalman,department from training join participation on training.id = trainingid join user on userid = user.id where month(startdate) = 10 group by department union select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,ojt.totalman,department from ojt join participateojt on ojt.id = ojtid where month(startdate) = 10 group by department)tablea)tablehours group by department)tableb on tablea.department = tableb.department;";
    $query_run = mysqli_query($conn, $query);

    if(mysqli_num_rows($query_run) > 0)
    {
        $sheet->mergeCells('AD1:AF1')->setCellValue('AD1', 'OCTOBER');
        $sheet->setCellValue('AD2', 'MAN POWER');
        $sheet->setCellValue('AE2', 'TOTAL TR HRS');
		$sheet->setCellValue('AF2', 'AVG TR HRS');
        $rowCountOrder = 3;
        foreach($query_run as $data)
        {
            $sheet->setCellValue('AD'.$rowCountOrder, $data['totalman']);
            $sheet->setCellValue('AE'.$rowCountOrder, $data['totalhour']);
            $sheet->setCellValue('AF'.$rowCountOrder, $data['avghours']);

            if ($data['avghours'] < 4) {
                $sheet->getStyle('AE'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF0000');
            }else if ($data['avghours'] >= 4) {
                $sheet->getStyle('AE'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92D050');
            }

            $rowCountOrder++;
        }
        $sheet->setCellValue('AD'.$rowCountOrder, '=SUM(AD3:AD'.($rowCountOrder-1).')');
        $sheet->setCellValue('AE'.$rowCountOrder, '=SUM(AE3:AE'.($rowCountOrder-1).')');
        $sheet->setCellValue('AE'.($rowCountOrder+1), '=AE'.($rowCountOrder).'/AD'.($rowCountOrder));

        $sheet->getStyle('AE'.($rowCountOrder+1))->getNumberFormat()->setFormatCode('0.00'); 
        $sheet->getColumnDimension('AD')->setAutoSize(true);
        $sheet->getColumnDimension('AE')->setAutoSize(true);
        $sheet->getColumnDimension('AF')->setAutoSize(true);
        $sheet->getStyle('AE'.($rowCountOrder))->getFont()->setBold( true );
        $sheet->getStyle('AE'.($rowCountOrder+1))->getFont()->setBold( true );
        $sheet->getStyle('AD1:AF'.($rowCountOrder-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    $query = "select tablea.department,ifnull(totalemp,0) as totalman,ifnull(totalhours,0) as totalhour,ifnull(round(totalhours/totalemp,2),0) as avghours from (select tablestaff.department,ifnull(totalstaff+totalcontract,totalstaff) as totalemp from (select department,count(*) as totalstaff from user group by department)tablestaff left join (select department,max(participateojt.totalman) as totalcontract from ojt join participateojt on ojt.id = ojtid where userid = 0 and month(startdate) = 11 group by department)tablecontract on tablestaff.department = tablecontract.department)tablea left join (select department, sum(lasttotal) as totalhours from (select department,totaldays * totalhours * totalman as lasttotal from (select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,count(*) as totalman,department from training join participation on training.id = trainingid join user on userid = user.id where month(startdate) = 11 group by department union select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,ojt.totalman,department from ojt join participateojt on ojt.id = ojtid where month(startdate) = 11 group by department)tablea)tablehours group by department)tableb on tablea.department = tableb.department;";
    $query_run = mysqli_query($conn, $query);

    if(mysqli_num_rows($query_run) > 0)
    {
        $sheet->mergeCells('AG1:AI1')->setCellValue('AG1', 'NOVEMBER');
        $sheet->setCellValue('AG2', 'MAN POWER');
        $sheet->setCellValue('AH2', 'TOTAL TR HRS');
		$sheet->setCellValue('AI2', 'AVG TR HRS');
        $rowCountOrder = 3;
        foreach($query_run as $data)
        {
            $sheet->setCellValue('AG'.$rowCountOrder, $data['totalman']);
            $sheet->setCellValue('AH'.$rowCountOrder, $data['totalhour']);
            $sheet->setCellValue('AI'.$rowCountOrder, $data['avghours']);

            if ($data['avghours'] < 4) {
                $sheet->getStyle('AI'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF0000');
            }else if ($data['avghours'] >= 4) {
                $sheet->getStyle('AI'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92D050');
            }

            $rowCountOrder++;
        }
        $sheet->setCellValue('AG'.$rowCountOrder, '=SUM(AG3:AG'.($rowCountOrder-1).')');
        $sheet->setCellValue('AH'.$rowCountOrder, '=SUM(AH3:AH'.($rowCountOrder-1).')');
        $sheet->setCellValue('AH'.($rowCountOrder+1), '=AH'.($rowCountOrder).'/AG'.($rowCountOrder));

        $sheet->getStyle('AH'.($rowCountOrder+1))->getNumberFormat()->setFormatCode('0.00'); 
        $sheet->getColumnDimension('AG')->setAutoSize(true);
        $sheet->getColumnDimension('AH')->setAutoSize(true);
        $sheet->getColumnDimension('AI')->setAutoSize(true);
        $sheet->getStyle('AH'.($rowCountOrder))->getFont()->setBold( true );
        $sheet->getStyle('AH'.($rowCountOrder+1))->getFont()->setBold( true );
        $sheet->getStyle('AG1:AI'.($rowCountOrder-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    $query = "select tablea.department,ifnull(totalemp,0) as totalman,ifnull(totalhours,0) as totalhour,ifnull(round(totalhours/totalemp,2),0) as avghours from (select tablestaff.department,ifnull(totalstaff+totalcontract,totalstaff) as totalemp from (select department,count(*) as totalstaff from user group by department)tablestaff left join (select department,max(participateojt.totalman) as totalcontract from ojt join participateojt on ojt.id = ojtid where userid = 0 and month(startdate) = 12 group by department)tablecontract on tablestaff.department = tablecontract.department)tablea left join (select department, sum(lasttotal) as totalhours from (select department,totaldays * totalhours * totalman as lasttotal from (select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,count(*) as totalman,department from training join participation on training.id = trainingid join user on userid = user.id where month(startdate) = 12 group by department union select (datediff(startdate, enddate)) - (( datediff(startdate, enddate) * 2) - case when dayname(startdate) = 'saturday' then 1 else 0 end - case when dayname(enddate) = 'sunday' then 1 else 0 end) + 1 as totaldays,round(TIME_TO_SEC(timediff(endtime,starttime))/3600,2) as totalhours,ojt.totalman,department from ojt join participateojt on ojt.id = ojtid where month(startdate) = 12 group by department)tablea)tablehours group by department)tableb on tablea.department = tableb.department;";
    $query_run = mysqli_query($conn, $query);

    if(mysqli_num_rows($query_run) > 0)
    {
        $sheet->mergeCells('AJ1:AL1')->setCellValue('AJ1', 'DECEMBER');
        $sheet->setCellValue('AJ2', 'MAN POWER');
        $sheet->setCellValue('AK2', 'TOTAL TR HRS');
		$sheet->setCellValue('AL2', 'AVG TR HRS');
        $rowCountOrder = 3;
        foreach($query_run as $data)
        {
            $sheet->setCellValue('AJ'.$rowCountOrder, $data['totalman']);
            $sheet->setCellValue('AK'.$rowCountOrder, $data['totalhour']);
            $sheet->setCellValue('AL'.$rowCountOrder, $data['avghours']);

            if ($data['avghours'] < 4) {
                $sheet->getStyle('AL'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF0000');
            }else if ($data['avghours'] >= 4) {
                $sheet->getStyle('AL'.$rowCountOrder)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92D050');
            }

            $rowCountOrder++;
        }
        $sheet->setCellValue('AJ'.$rowCountOrder, '=SUM(AJ3:AJ'.($rowCountOrder-1).')');
        $sheet->setCellValue('AK'.$rowCountOrder, '=SUM(AK3:AK'.($rowCountOrder-1).')');
        $sheet->setCellValue('AK'.($rowCountOrder+1), '=AK'.($rowCountOrder).'/AJ'.($rowCountOrder));

        $sheet->getStyle('AK'.($rowCountOrder+1))->getNumberFormat()->setFormatCode('0.00'); 
        $sheet->getColumnDimension('AJ')->setAutoSize(true);
        $sheet->getColumnDimension('AK')->setAutoSize(true);
        $sheet->getColumnDimension('AL')->setAutoSize(true);
        $sheet->getStyle('AK'.($rowCountOrder))->getFont()->setBold( true );
        $sheet->getStyle('AK'.($rowCountOrder+1))->getFont()->setBold( true );
        $sheet->getStyle('AJ1:AL'.($rowCountOrder-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    $sheet->getStyle('C1:AJ1')->getAlignment()->setHorizontal('center');
    $sheet->getStyle('A1:AL2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('D9D9D9');
    $sheet->getStyle('A1:AL2')->getFont()->setBold( true );
    
    $year = date("Y");
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attactment; filename="Training Hours '.$year.'.xlsx"');
    $writer->save('php://output');
?>