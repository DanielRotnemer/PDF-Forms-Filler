<?php

    session_start();
    date_default_timezone_set("Asia/Jerusalem");    
    require_once('C:\xampp\htdocs\utils\utils.php');
    require_once('C:\xampp\htdocs\vendor\autoload.php');

    // SAVE UPLOADED FILE
    if (isset($_POST['fileData']) && isset($_POST['fileName']))
    {
        $fileParts = explode(";base64,", $_POST['fileData']);
        $fileBase64 = base64_decode($fileParts[1]);         
        file_put_contents('C:\xampp\htdocs\pdfForms\\'.$_POST['fileName'], $fileBase64);        
        echo 'http://'.$GLOBALS['SERVER_ADDRESS'].'/pdfForms/'.$_POST['fileName'];
        exit;
    }

    // SAVE SELECTED REGIONS
    if (isset($_POST['selectedFields']) && isset($_POST['coordinates']) && isset($_POST['fileName']) && isset($_POST['pWidth']) && isset($_POST['pHeight']))
    {
        $fields = json_decode(stripslashes($_POST['selectedFields']));
        $coordinates = json_decode(stripslashes($_POST['coordinates']));

        // SAVE FILE SIZE IN THE DATABASE
        $pdo = UTILITIES::PDO_DB_Connection('files');
        $stmt = $pdo->prepare("INSERT INTO files_table (FileName,PageWidth,PageHeight) VALUES (?,?,?)");
        $stmt->execute([$_POST['fileName'], $_POST['pWidth'], $_POST['pHeight']]);
        $pdo = null;
        
        // SAVE THE DATA IN THE DATABASE
        $pdo = UTILITIES::PDO_DB_Connection('regions');
        foreach ($fields as $key => $value)
        {
            $field = count($value) == 1 ? $value[0][0].'|'.$value[0][1] :
                $value[0][0].'|'.$value[0][1].','.$value[1][0].'|'.$value[1][1];
            $stmt = $pdo->prepare("INSERT INTO regions_table (FileName,TopCoordinate,LeftCoordinate,PageNumber,Field) VALUES (?,?,?,?,?)");
            $stmt->execute([$_POST['fileName'], $coordinates[$key][1], $coordinates[$key][2], $coordinates[$key][0], $field]);
        }        
        $pdo = null;

        echo 'נשמר בהצלחה';
        exit;
    }

    // EXPORT FILES
    if (isset($_POST['associationId']) && isset($_POST['fileName']))
    {
        // SELECT ALL THE REGIONS OF THIS FILE FROM THE DATABSE
        $pdo = UTILITIES::PDO_DB_Connection('regions');
        $stmt = $pdo->prepare("SELECT * FROM regions_table WHERE FileName=?");
        $stmt->execute([$_POST['fileName']]);
        $regionsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        // SELECT ALL THE WORKERS OF THIS ASSOCIATIONS
        $pdo = UTILITIES::PDO_DB_Connection('roles');
        $stmt = $pdo->prepare("SELECT DISTINCT(WorkerId) FROM roles_table WHERE AssociationId=?");
        $stmt->execute([$_POST['associationId']]);
        $workerIdsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        // SELECT THE DETAILS ABOUT THIS ASSOCIATION
        $pdo = UTILITIES::PDO_DB_Connection('associations');
        $stmt = $pdo->prepare("SELECT * FROM associations_table WHERE BINARY AssociationId=?");
        $stmt->execute([$_POST['associationId']]);
        $associationResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        $hasWorkerField = false;
        foreach ($regionsResult as $key => $value)
        {
            if (substr($value['Field'], 0, 7) == "Worker|") {
                $hasWorkerField = true;
                break;
            }
        }

        if ($hasWorkerField == true)
        {
            foreach ($workerIdsResult as $wKey => $wValue)
            {
                $regionsData = [];
                foreach ($regionsResult as $rKey => $rValue)
                {
                    if (strpos($rValue['Field'], ',') !== false)
                    {
                        $fields = explode(',', $rValue['Field']);
                        $role = substr($fields[0], strpos($fields[0], '|') + 1);
                        $workerDetail = substr($fields[1], strpos($fields[1], '|') + 1);
                        
                        // SELECT THE WORKER ID FOR THIS ROLE IN THIS ASSOCIATION
                        $pdo = UTILITIES::PDO_DB_Connection('roles');
                        $stmt = $pdo->prepare("SELECT WorkerId FROM roles_table WHERE BINARY AssociationId=? AND RoleName=?");
                        $stmt->execute([$_POST['associationId'], $role]);
                        $workerResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $pdo = null;
    
                        $workerValue = '';
                        if (count($workerResult) > 0)
                        {
                            // SELECT THE WORKER ID FOR THIS ROLE IN THIS ASSOCIATION
                            $pdo = UTILITIES::PDO_DB_Connection('workers');
                            $stmt = $pdo->prepare("SELECT ".$workerDetail." FROM workers_table WHERE BINARY WorkerId=?");
                            $stmt->execute([$workerResult[0]['WorkerId']]);
                            $workerValueResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $pdo = null;
    
                            if (count($workerValueResult) > 0) {
                                $workerValue = $workerValueResult[0][$workerDetail];
                            }
                        }
                        array_push($regionsData, [$workerValue, intval($rValue['TopCoordinate']), intval($rValue['LeftCoordinate']), intval($rValue['RegionHeight']), intval($rValue['RegionWidth']), intval($rValue['PageNumber']) + 1]); // filedValue, top, left, pageIndex
                    }
                    else 
                    {
                        $column = substr($rValue['Field'], strpos($rValue['Field'], '|') + 1);
                        $type = substr($rValue['Field'], 0, strpos($rValue['Field'], '|'));

                        $db = $type == 'Association' ? 'associations' : 'workers';
                        $table = $type == 'Association' ? 'associations_table' : 'workers_table';
                        $where = $type == 'Association' ? 'AssociationId' : 'WorkerId';
                        $whereValue = $type == 'Association' ? $_POST['associationId'] : $wValue['WorkerId'];
    
                        // SELECT FIELD DATA FROM THE DATABASE
                        $pdo = UTILITIES::PDO_DB_Connection($db);
                        $stmt = $pdo->prepare("SELECT ".$column." FROM ".$table." WHERE BINARY ".$where."=?");
                        $stmt->execute([$whereValue]);
                        $valueResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $pdo = null;
                        
                        $fieldValue = '';
                        if (count($valueResult) > 0) {
                            $fieldValue = $valueResult[0][$column];
                        }
                        array_push($regionsData, [$fieldValue, intval($rValue['TopCoordinate']), intval($rValue['LeftCoordinate']), intval($rValue['RegionHeight']), intval($rValue['RegionWidth']), intval($rValue['PageNumber']) + 1]); // filedValue, top, left, pageIndex
                    }
                }
    
                // SELECT THE FILE SIZE FROM THE DATABASE
                $fileName = 'C:\xampp\htdocs\pdfForms\\'.$_POST['fileName'];
                $pdo = UTILITIES::PDO_DB_Connection('files');
                $stmt = $pdo->prepare("SELECT PageWidth,PageHeight FROM files_table WHERE BINARY FileName=?");
                $stmt->execute([$_POST['fileName']]);
                $fileSizeResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $pdo = null;
    
                $pageWidth = count($fileSizeResult) > 0 ? intval($fileSizeResult[0]['PageWidth']) : 773;
                $pageHeihgt = count($fileSizeResult) > 0 ? intval($fileSizeResult[0]['PageHeight']) : 1094;
    
                $pdf = new \setasign\Fpdi\Fpdi();
    
                // set the source file
                $pageCount = $pdf->setSourceFile($fileName);
    
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) 
                {
                    $tplIdx = $pdf->importPage($pageNo);
    
                    $specs = $pdf->getTemplateSize($tplIdx); 
                    // add a page
                    $pdf->AddPage("P", array($pageWidth, $pageHeihgt));
                    // use the imported page as the template
                    $pdf->useTemplate($tplIdx, null, null, $pageWidth, $pageHeihgt, false);
    
                    foreach ($regionsData as $rdKey => $rdValue)
                    {
                        if ($rdValue[5] == $pageNo)
                        {
                            $pdf->AddFont('OpenSansHebrew-Regular', '', 'OpenSansHebrew-Regular.php');
                            $pdf->SetFont('OpenSansHebrew-Regular', '', 10);
                            $pdf->SetFontSize('35');
                            $pdf->SetTextColor(0, 0, 0);

                            $isHebrew = false;
                            for ($i = 0; $i < strlen($rdValue[0]); $i++)
                            {
                                if (preg_match('/[א-ת]/', $rdValue[0][$i])) {
                                    $isHebrew = true;
                                    break;
                                }
                            }
                            
                            $formattedValue = iconv('UTF-8', 'cp1255',  $rdValue[0]);
                            if ($isHebrew == true) {
                                $formattedValue = strrev($formattedValue);
                            }

                            // now write some text above the imported page
                            $pdf->SetXY($rdValue[2], $rdValue[1]);
                            $pdf->Write(/*$rdValue[3]*/6, $formattedValue);
                        }
                    }
                }
                $src = 'C:\xampp\htdocs\pdfForms\\'.$_POST['fileName'];
                $dest = 'C:\xampp\htdocs\export\\'.str_replace('.pdf', '', $_POST['fileName']).' '.$wKey.' '.date('d-m-Y H-i-s').'.pdf';
                copy($src, $dest);
                $pdf->Output($dest, 'F');
            }
        }
        else 
        {            
            $regionsData = [];
            foreach ($regionsResult as $rKey => $rValue)
            {
                if (strpos($rValue['Field'], ',') !== false)
                {
                    $fields = explode(',', $rValue['Field']);
                    $role = substr($fields[0], strpos($fields[0], '|') + 1);
                    $workerDetail = substr($fields[1], strpos($fields[1], '|') + 1);
                    
                    // SELECT THE WORKER ID FOR THIS ROLE IN THIS ASSOCIATION
                    $pdo = UTILITIES::PDO_DB_Connection('roles');
                    $stmt = $pdo->prepare("SELECT WorkerId FROM roles_table WHERE BINARY AssociationId=? AND RoleName=?");
                    $stmt->execute([$_POST['associationId'], $role]);
                    $workerResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $pdo = null;

                    $workerValue = '';
                    if (count($workerResult) > 0)
                    {
                        // SELECT THE WORKER ID FOR THIS ROLE IN THIS ASSOCIATION
                        $pdo = UTILITIES::PDO_DB_Connection('workers');
                        $stmt = $pdo->prepare("SELECT ".$workerDetail." FROM workers_table WHERE BINARY WorkerId=?");
                        $stmt->execute([$workerResult[0]['WorkerId']]);
                        $workerValueResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $pdo = null;

                        if (count($workerValueResult) > 0) {
                            $workerValue = $workerValueResult[0][$workerDetail];
                        }
                    }
                    array_push($regionsData, [$workerValue, intval($rValue['TopCoordinate']), intval($rValue['LeftCoordinate']), intval($rValue['RegionHeight']), intval($rValue['RegionWidth']), intval($rValue['PageNumber']) + 1]); // filedValue, top, left, pageIndex
                }
                else 
                {
                    $column = substr($rValue['Field'], strpos($rValue['Field'], '|') + 1);

                    // SELECT FIELD DATA FROM THE DATABASE
                    $pdo = UTILITIES::PDO_DB_Connection('associations');
                    $stmt = $pdo->prepare("SELECT ".$column." FROM associations_table WHERE BINARY AssociationId=?");
                    $stmt->execute([$_POST['associationId']]);
                    $associationValueResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $pdo = null;
                    
                    $associationValue = '';
                    if (count($associationValueResult) > 0) {
                        $associationValue = $associationValueResult[0][$column];
                    }
                    array_push($regionsData, [$associationValue, intval($rValue['TopCoordinate']), intval($rValue['LeftCoordinate']), intval($rValue['RegionHeight']), intval($rValue['RegionWidth']), intval($rValue['PageNumber']) + 1]); // filedValue, top, left, pageIndex
                }
            }

            // SELECT THE FILE SIZE FROM THE DATABASE
            $fileName = 'C:\xampp\htdocs\pdfForms\\'.$_POST['fileName'];
            $pdo = UTILITIES::PDO_DB_Connection('files');
            $stmt = $pdo->prepare("SELECT PageWidth,PageHeight FROM files_table WHERE BINARY FileName=?");
            $stmt->execute([$_POST['fileName']]);
            $fileSizeResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null;

            $pageWidth = count($fileSizeResult) > 0 ? intval($fileSizeResult[0]['PageWidth']) : 773;
            $pageHeihgt = count($fileSizeResult) > 0 ? intval($fileSizeResult[0]['PageHeight']) : 1094;

            $pdf = new \setasign\Fpdi\Fpdi();

            // set the source file
            $pageCount = $pdf->setSourceFile($fileName);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) 
            {
                $tplIdx = $pdf->importPage($pageNo);

                $specs = $pdf->getTemplateSize($tplIdx); 
                // add a page
                $pdf->AddPage("P", array($pageWidth, $pageHeihgt));
                // use the imported page as the template
                $pdf->useTemplate($tplIdx, null, null, $pageWidth, $pageHeihgt, false);

                foreach ($regionsData as $rdKey => $rdValue)
                {
                    if ($rdValue[5] == $pageNo)
                    {
                        // font and color selection
                        $pdf->AddFont('OpenSansHebrew-Regular', '', 'OpenSansHebrew-Regular.php');
                        $pdf->SetFont('OpenSansHebrew-Regular', '', 10);
                        $pdf->SetFontSize('35');
                        $pdf->SetTextColor(0, 0, 0);

                        $isHebrew = false;
                        for ($i = 0; $i < strlen($rdValue[0]); $i++)
                        {
                            if (preg_match('/[א-ת]/', $rdValue[0][$i])) {
                                $isHebrew = true;
                                break;
                            }
                        }
                        
                        $formattedValue = iconv('UTF-8', 'cp1255',  $rdValue[0]);
                        if ($isHebrew == true) {
                            $formattedValue = strrev($formattedValue);
                        }

                        // now write some text above the imported page                       
                        $pdf->SetXY($rdValue[2], $rdValue[1]);
                        $pdf->Write(/*$rdValue[3]*/6, $formattedValue);
                    }
                }
            }
            $src = 'C:\xampp\htdocs\pdfForms\\'.$_POST['fileName'];
            $dest = 'C:\xampp\htdocs\export\\'.str_replace('.pdf', '', $_POST['fileName']).' '.date('d-m-Y H-i-s').'.pdf';
            copy($src, $dest);
            $pdf->Output($dest, 'F');
        }  
       
        echo 'היצוא הושלם בהצלחה, גש לתיקיה export על מנת לפתוח את הקבצים';
    }

?>