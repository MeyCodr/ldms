<?php

    include "../../../dbconn.php";

    require '../../../asset/fpdf/fpdf.php';

    if($_POST["action"] == "printpdf"){
        $userid = $_POST["userid"];

        $sqla = "select staffno,staffname,user.department,user.section,DATE_FORMAT(curdate(),'%d/%m/%Y') as date1 from user join tna on user.id = userid where user.id = '$userid';";
        $querya = mysqli_query($conn,$sqla);
        while($rowa = mysqli_fetch_assoc($querya))
        {
            $staffno = $rowa['staffno'];
            $staffname = $rowa['staffname'];
            $section = $rowa['section'];
            $department = $rowa['department'];
            $date1 = $rowa['date1'];
        }

        $sqlb = "select ifnull(sum(totalday*totalhour),0) as sumhour from (select ((DATEDIFF(enddate, startdate)) + 1) as totalday,ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour from training join participation on training.id = trainingid where userid = '$userid')tablea;";
        $queryb = mysqli_query($conn,$sqlb);
        while($rowb = mysqli_fetch_assoc($queryb))
        {
            $publichour = $rowb['sumhour'];
        }

        $sqlc = "select ifnull(sum(totalday*totalhour),0) as sumhour from (select ((DATEDIFF(enddate, startdate)) + 1) as totalday,ROUND((TIME_TO_SEC(TIMEDIFF(endtime,starttime))/60)/60,2) as totalhour from ojt join participateojt on ojt.id = ojtid where userid = '$userid')tablea;";
        $queryc = mysqli_query($conn,$sqlc);
        while($rowc = mysqli_fetch_assoc($queryc))
        {
            $ojthour = $rowc['sumhour'];
        }

        $sqld = "select staffname from user where department = '$department' and usertype = 'HOD';";
        $queryd = mysqli_query($conn,$sqld);
        while($rowd = mysqli_fetch_assoc($queryd))
        {
            $hodname = $rowd['staffname'];
        }

        class PDF extends FPDF {
            function Header() {
                // Logo
                $this->Image('../../../asset/image/phn_logo.png',25,6,30);
                // Arial bold 15
                $this->SetFont('Arial','B',20);
                // Move to the right
                $this->Cell(45);
                // Title
                $this->Cell(30,7,'Learning & Development Department');
                $this->Ln(15);
                $this->SetFont('Arial','B',12);
                $this->Cell(45);
                $this->Cell(0,5,'Individual Training Needs Analysis(TNA) 2024');
                // Line break
                $this->Ln(8);
            }

            // variable to store widths and aligns of cells, and line height
            var $widths;
            var $aligns;
            var $lineHeight;

            //Set the array of column widths
            function SetWidths($w) {
                $this->widths=$w;
            }

            //Set the array of column alignments
            function SetAligns($a) {
                $this->aligns=$a;
            }

            //Set line height
            function SetLineHeight($h) {
                $this->lineHeight=$h;
            }

            //Calculate the height of the row
            function Row($data) {
                // number of line
                $nb=0;

                // loop each data to find out greatest line number in a row.
                for($i=0;$i<count($data);$i++){
                    // NbLines will calculate how many lines needed to display text wrapped in specified width.
                    // then max function will compare the result with current $nb. Returning the greatest one. And reassign the $nb.
                    $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
                }
                
                //multiply number of line with line height. This will be the height of current row
                $h=$this->lineHeight * $nb;

                //Issue a page break first if needed
                $this->CheckPageBreak($h);

                //Draw the cells of current row
                for($i=0;$i<count($data);$i++) {
                    // width of the current col
                    $w=$this->widths[$i];
                    // alignment of the current col. if unset, make it left.
                    $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
                    //Save the current position
                    $x=$this->GetX();
                    $y=$this->GetY();
                    //Draw the border
                    $this->Rect($x,$y,$w,$h);
                    //Print the text
                    $this->MultiCell($w,5,$data[$i],0,$a);
                    //Put the position to the right of the cell
                    $this->SetXY($x+$w,$y);
                }
                //Go to the next line
                $this->Ln($h);
            }

            function CheckPageBreak($h) {
                //If the height h would cause an overflow, add a new page immediately
                if($this->GetY()+$h>$this->PageBreakTrigger)
                    $this->AddPage($this->CurOrientation);
            }

            function NbLines($w,$txt) {
                //calculate the number of lines a MultiCell of width w will take
                $cw=&$this->CurrentFont['cw'];
                if($w==0)
                    $w=$this->w-$this->rMargin-$this->x;
                $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
                $s=str_replace("\r",'',$txt);
                $nb=strlen($s);
                if($nb>0 and $s[$nb-1]=="\n")
                    $nb--;
                $sep=-1;
                $i=0;
                $j=0;
                $l=0;
                $nl=1;
                while($i<$nb) {
                    $c=$s[$i];
                    if($c=="\n") {
                        $i++;
                        $sep=-1;
                        $j=$i;
                        $l=0;
                        $nl++;
                        continue;
                    }
                    if($c==' ')
                        $sep=$i;
                    $l+=$cw[$c];
                    if($l>$wmax) {
                        if($sep==-1) {
                            if($i==$j)
                                $i++;
                        }
                        else
                            $i=$sep+1;
                        $sep=-1;
                        $j=$i;
                        $l=0;
                        $nl++;
                    }
                    else
                        $i++;
                }
                return $nl;
            }
        }

        $pdf = new PDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(150,10,'');
        $pdf->SetFont('Arial','U',10);
        $pdf->Cell(0,10,'Training History 2023');
        $pdf->SetFont('Arial','',10);
        $pdf->Ln(5);
        $pdf->Cell(20,10,'Staff No.');
        $pdf->Cell(130,10,': '.$staffno);
        $pdf->Cell(20,10,'Total Hours');
        $pdf->Cell(0,10,': '.$publichour+$ojthour);
        $pdf->Ln(5);
        $pdf->Cell(20,10,'Staff Name');
        $pdf->Cell(130,10,': '.$staffname);
        $pdf->Cell(20,10,'Total Public');
        $pdf->Cell(0,10,': '.$publichour);
        $pdf->Ln(5);
        $pdf->Cell(20,10,'Section');
        $pdf->Cell(130,10,': '.$section);
        $pdf->Cell(20,10,'Total OJT');
        $pdf->Cell(0,10,': '.$ojthour);

        $pdf->Ln(15);
        $pdf->SetWidths(Array(56,56,15,15,10,25,13));
        $pdf->SetLineHeight(5);
        // $pdf->Cell(0,5,'Area : Mandatory',1);
        // $pdf->Ln();

        // $pdf->SetWidths(Array(56,56,15,15,10,25,13));
        // $pdf->SetLineHeight(5);

        // $pdf->Cell(56,5,'Major Task',1);
        // $pdf->Cell(56,5,'Training Required',1);
        // $pdf->Cell(15,5,'Target',1);
        // $pdf->Cell(15,5,'Current',1);
        // $pdf->Cell(10,5,'Gap',1);
        // $pdf->Cell(25,5,'Training Type',1);
        // $pdf->Cell(13,5,'When',1);
        // $pdf->Ln();

        // $sql = "select * from tna where userid = '$userid' and section = 'mandatory' and year = year(curdate());";
        // $query = mysqli_query($conn,$sql);
        // while($row = mysqli_fetch_assoc($query))
        // {
        //     if ($row['trainingtype'] == 1) {
        //         $trtype = 'ON JOB TRAINING';
        //     }else if ($row['trainingtype'] == 2) {
        //         $trtype = 'EXTERNAL / IN-HOUSE';
        //     }

        //     $output[]= array(
        //         'task' => $row['task'],
        //         'training' => $row['training'],
        //         'targetskill' => $row['targetskill'],
        //         'currentskill' => $row['currentskill'],
        //         'gap' => $row['gap'],
        //         'trainingtype' => $trtype,
        //         'monthapply' => $row['monthapply']
        //     );
        // }

        // $data = $output;

        // //loop the data
        // foreach($data as $item) {
        //     //write data using Row() method containing array of values.
        //     $pdf->Row(Array(
        //         $item['task'],
        //         $item['training'],
        //         $item['targetskill'],
        //         $item['currentskill'],
        //         $item['gap'],
        //         $item['trainingtype'],
        //         $item['monthapply'],
        //     ));
        // }

        // $pdf->Ln(5);
        $pdf->Cell(0,5,'Area : Self Awareness',1);
        $pdf->Ln();

        $pdf->Cell(56,5,'Major Task',1);
        $pdf->Cell(56,5,'Training Required',1);
        $pdf->Cell(15,5,'Target',1);
        $pdf->Cell(15,5,'Current',1);
        $pdf->Cell(10,5,'Gap',1);
        $pdf->Cell(25,5,'Training Type',1);
        $pdf->Cell(13,5,'When',1);
        $pdf->Ln();

        $output1 = [];

        $sql1 = "select * from tna where userid = '$userid' and section = 'selfaware' and year = year(curdate());";
        $query1 = mysqli_query($conn,$sql1);
        while($row1 = mysqli_fetch_assoc($query1))
        {
            if ($row1['trainingtype'] == 1) {
                $trtype = 'ON JOB TRAINING';
            }else if ($row1['trainingtype'] == 2) {
                $trtype = 'EXTERNAL / IN-HOUSE';
            }

            if ($row1['training'] == 'OTHERS') {
                $trainingfinal1 = $row1['othertr'];
            }else {
                $trainingfinal1 = $row1['training'];
            }

            $output1[]= array(
                'task' => $row1['task'],
                'training' => $trainingfinal1,
                'targetskill' => $row1['targetskill'],
                'currentskill' => $row1['currentskill'],
                'gap' => $row1['gap'],
                'trainingtype' => $trtype,
                'monthapply' => $row1['monthapply']
            );
        }

        if (count($output1) === 0) {
            $pdf->Cell(0,5,'No record found for this area',1);
            $pdf->Ln(5);
        }else {
            $data1 = $output1;
            //loop the data
            foreach($data1 as $item1) {
                //write data using Row() method containing array of values.
                $pdf->Row(Array(
                    $item1['task'],
                    $item1['training'],
                    $item1['targetskill'],
                    $item1['currentskill'],
                    $item1['gap'],
                    $item1['trainingtype'],
                    $item1['monthapply'],
                ));
            }
        }

        $pdf->Ln(5);
        $pdf->Cell(0,5,'Area : Leadership Awareness',1);
        $pdf->Ln();

        $pdf->Cell(56,5,'Major Task',1);
        $pdf->Cell(56,5,'Training Required',1);
        $pdf->Cell(15,5,'Target',1);
        $pdf->Cell(15,5,'Current',1);
        $pdf->Cell(10,5,'Gap',1);
        $pdf->Cell(25,5,'Training Type',1);
        $pdf->Cell(13,5,'When',1);
        $pdf->Ln();

        $output2 = [];

        $sql2 = "select * from tna where userid = '$userid' and section = 'leadaware' and year = year(curdate());";
        $query2 = mysqli_query($conn,$sql2);
        while($row2 = mysqli_fetch_assoc($query2))
        {
            if ($row2['trainingtype'] == 1) {
                $trtype = 'ON JOB TRAINING';
            }else if ($row2['trainingtype'] == 2) {
                $trtype = 'EXTERNAL / IN-HOUSE';
            }

            if ($row2['training'] == 'OTHERS') {
                $trainingfinal2 = $row2['othertr'];
            }else {
                $trainingfinal2 = $row2['training'];
            }

            $output2[]= array(
                'task' => $row2['task'],
                'training' => $trainingfinal2,
                'targetskill' => $row2['targetskill'],
                'currentskill' => $row2['currentskill'],
                'gap' => $row2['gap'],
                'trainingtype' => $trtype,
                'monthapply' => $row2['monthapply']
            );
        }

        if (count($output2) === 0) {
            $pdf->Cell(0,5,'No record found for this area',1);
            $pdf->Ln(5);
        }else {
            $data2 = $output2;
            //loop the data
            foreach($data2 as $item2) {
                //write data using Row() method containing array of values.
                $pdf->Row(Array(
                    $item2['task'],
                    $item2['training'],
                    $item2['targetskill'],
                    $item2['currentskill'],
                    $item2['gap'],
                    $item2['trainingtype'],
                    $item2['monthapply'],
                ));
            }
        }

        $pdf->Ln(5);
        $pdf->Cell(0,5,'Area : Functional Awareness',1);
        $pdf->Ln();

        $pdf->Cell(56,5,'Major Task',1);
        $pdf->Cell(56,5,'Training Required',1);
        $pdf->Cell(15,5,'Target',1);
        $pdf->Cell(15,5,'Current',1);
        $pdf->Cell(10,5,'Gap',1);
        $pdf->Cell(25,5,'Training Type',1);
        $pdf->Cell(13,5,'When',1);
        $pdf->Ln();

        $output3 = [];

        $sql3 = "select * from tna where userid = '$userid' and section = 'functional' and year = year(curdate());";
        $query3 = mysqli_query($conn,$sql3);
        while($row3 = mysqli_fetch_assoc($query3))
        {
            if ($row3['trainingtype'] == 1) {
                $trtype = 'ON JOB TRAINING';
            }else if ($row3['trainingtype'] == 2) {
                $trtype = 'EXTERNAL / IN-HOUSE';
            }

            if ($row3['training'] == 'OTHERS') {
                $trainingfinal3 = $row3['othertr'];
            }else {
                $trainingfinal3 = $row3['training'];
            }

            $output3[]= array(
                'task' => $row3['task'],
                'training' => $trainingfinal3,
                'targetskill' => $row3['targetskill'],
                'currentskill' => $row3['currentskill'],
                'gap' => $row3['gap'],
                'trainingtype' => $trtype,
                'monthapply' => $row3['monthapply']
            );
        }

        if (count($output3) === 0) {
            $pdf->Cell(0,5,'No record found for this area',1);
            $pdf->Ln(5);
        }else {
            $data3 = $output3;
            //loop the data
            foreach($data3 as $item3) {
                //write data using Row() method containing array of values.
                $pdf->Row(Array(
                    $item3['task'],
                    $item3['training'],
                    $item3['targetskill'],
                    $item3['currentskill'],
                    $item3['gap'],
                    $item3['trainingtype'],
                    $item3['monthapply'],
                ));
            }
        }

        $pdf->Ln(5);
        $pdf->Cell(0,5,'Area : Business Awareness',1);
        $pdf->Ln();

        $pdf->Cell(56,5,'Major Task',1);
        $pdf->Cell(56,5,'Training Required',1);
        $pdf->Cell(15,5,'Target',1);
        $pdf->Cell(15,5,'Current',1);
        $pdf->Cell(10,5,'Gap',1);
        $pdf->Cell(25,5,'Training Type',1);
        $pdf->Cell(13,5,'When',1);
        $pdf->Ln();

        $output4 = [];

        $sql4 = "select * from tna where userid = '$userid' and section = 'busiaware' and year = year(curdate());";
        $query4 = mysqli_query($conn,$sql4);
        while($row4 = mysqli_fetch_assoc($query4))
        {
            if ($row4['trainingtype'] == 1) {
                $trtype = 'ON JOB TRAINING';
            }else if ($row4['trainingtype'] == 2) {
                $trtype = 'EXTERNAL / IN-HOUSE';
            }

            if ($row4['training'] == 'OTHERS') {
                $trainingfinal4 = $row4['othertr'];
            }else {
                $trainingfinal4 = $row4['training'];
            }

            $output4[]= array(
                'task' => $row4['task'],
                'training' => $trainingfinal4,
                'targetskill' => $row4['targetskill'],
                'currentskill' => $row4['currentskill'],
                'gap' => $row4['gap'],
                'trainingtype' => $trtype,
                'monthapply' => $row4['monthapply']
            );
        }

        if (count($output4) === 0) {
            $pdf->Cell(0,5,'No record found for this area',1);
            $pdf->Ln(5);
        }else {
            $data4 = $output4;
            //loop the data
            foreach($data4 as $item4) {
                //write data using Row() method containing array of values.
                $pdf->Row(Array(
                    $item4['task'],
                    $item4['training'],
                    $item4['targetskill'],
                    $item4['currentskill'],
                    $item4['gap'],
                    $item4['trainingtype'],
                    $item4['monthapply'],
                ));
            }
        }

        $pdf->Ln(5);
        $pdf->Cell(0,5,'Area : Special Project',1);
        $pdf->Ln();

        $pdf->Cell(56,5,'Major Task',1);
        $pdf->Cell(56,5,'Training Required',1);
        $pdf->Cell(15,5,'Target',1);
        $pdf->Cell(15,5,'Current',1);
        $pdf->Cell(10,5,'Gap',1);
        $pdf->Cell(25,5,'Training Type',1);
        $pdf->Cell(13,5,'When',1);
        $pdf->Ln();

        $output5 = [];

        $sql5 = "select * from tna where userid = '$userid' and section = 'special' and year = year(curdate());";
        $query5 = mysqli_query($conn,$sql5);
        while($row5 = mysqli_fetch_assoc($query5))
        {
            if ($row5['trainingtype'] == 1) {
                $trtype = 'ON JOB TRAINING';
            }else if ($row5['trainingtype'] == 2) {
                $trtype = 'EXTERNAL / IN-HOUSE';
            }

            if ($row5['training'] == 'OTHERS') {
                $trainingfinal5 = $row5['othertr'];
            }else {
                $trainingfinal5 = $row5['training'];
            }

            $output5[]= array(
                'task' => $row5['task'],
                'training' => $trainingfinal5,
                'targetskill' => $row5['targetskill'],
                'currentskill' => $row5['currentskill'],
                'gap' => $row5['gap'],
                'trainingtype' => $trtype,
                'monthapply' => $row5['monthapply']
            );
        }

        if (count($output5) === 0) {
            $pdf->Cell(0,5,'No record found for this area',1);
            $pdf->Ln(5);
        }else {
            $data5 = $output5;
            //loop the data
            foreach($data5 as $item5) {
                //write data using Row() method containing array of values.
                $pdf->Row(Array(
                    $item5['task'],
                    $item5['training'],
                    $item5['targetskill'],
                    $item5['currentskill'],
                    $item5['gap'],
                    $item5['trainingtype'],
                    $item5['monthapply'],
                ));
            }
        }

        $pdf->Ln(3);
        $pdf->Cell(0,20,'Approved by Head of Department');
        $pdf->Ln(13);
        $pdf->Cell(10,5,'Name');
        $pdf->Cell(0,5,': '.$hodname);
        $pdf->Ln(5);
        $pdf->Cell(23,5,'Date Approve');
        $pdf->Cell(0,5,': '.$date1);

        $pdf->Output('../../../asset/tnapdf/TNA - '.$staffname.'.pdf','F');
        $filename = '../../../asset/tnapdf/TNA - '.$staffname.'.pdf';

        echo $filename;
    }
?>