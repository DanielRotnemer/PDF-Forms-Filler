<?php

    header('Content-Type: text/html;charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    date_default_timezone_set("Asia/Jerusalem");

    require_once('C:\xampp\htdocs\utils\utils.php');

    /*if (count($_COOKIE) === 0 || !isset($_COOKIE[session_name()]) || !file_exists('C:\xampp\tmp\sess_'.$_COOKIE[session_name()])) {
        session_id(UTILITIES::CreateSessionId());
    }
    else {
        session_id($_COOKIE[session_name()]);   
    }*/
    session_start();

    if (!isset($_SESSION['Username']))
    {
        header("Location: http://$GLOBALS[SERVER_ADDRESS]/signin");
        exit;
    }

    // SELECT ALL THE ASSOCIATIONS FROM THE DATABASE    
    $pdo = UTILITIES::PDO_DB_Connection('associations');
    $stmt = $pdo->prepare("SELECT AssociationId,AssociationName,AssociationNumber FROM associations_table");
    $stmt->execute();
    $associationsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $pdo = null;

    $associations = '';
    foreach ($associationsResult as $key => $value)
    {
        $border = $key == count($associationsResult) - 1 ? ' style="border-bottom: none;"' : '';
        $associations .=
        '<div associationId="'.$value['AssociationId'].'" class="menu-item-w noselect" itmType="association"'.$border.'>
            <div class="menu-item-text">'.$value['AssociationName'].' - '.$value['AssociationNumber'].'</div>
        </div>';
    }

    // SELECT ALL THE FILES FROM THE DATABASE    
    $pdo = UTILITIES::PDO_DB_Connection('regions');
    $stmt = $pdo->prepare("SELECT DISTINCT(FileName) FROM regions_table");
    $stmt->execute();
    $filesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $pdo = null;

    $fileNames = '';
    foreach ($filesResult as $key => $value)
    {
        $border = $key == count($filesResult) - 1 ? ' style="border-bottom: none;"' : '';
        $fileNames .=
        '<div fileName="'.$value['FileName'].'" class="menu-item-w noselect" itmType="file"'.$border.'>
            <div class="menu-item-text">'.str_replace('.pdf', '', $value['FileName']).'</div>
        </div>';
    }

?>

<html>
    <head>
        <title>יצוא טפסים</title>
        <link rel="stylesheet" href="http://<?php echo $GLOBALS['SERVER_ADDRESS']; ?>/exportPages.css?c=<?php echo time(); ?>"/>
        <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@2.10.377/build/pdf.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/pdf-lib@1.16.0/dist/pdf-lib.min.js"></script>
        <script type="text/javascript" src="http://<?php echo $GLOBALS['SERVER_ADDRESS']; ?>/exportPages.js?c=<?php echo time(); ?>"></script>        
    </head>
    <body>

        <div class="header">
            <div class="header-text">יצוא טפסים</div>
        </div>
        <div class="body-wrapper">
            <div class="form-w">
                <div class="inner-form-w">

                    <div class="text">להעלאת קבצים <a target="_blank" href="upload.php">לחץ כאן</a></div>
                    <div class="text" style="margin-top: 10px;">להוספת עמותה <a target="_blank" href="newAssociation.php">לחץ כאן</a></div>
                    <div class="text" style="margin-top: 10px;">להוספת עובד <a target="_blank" href="newWorker.php">לחץ כאן</a></div>
                    <div class="text" style="margin-top: 10px;">להוספת תפקיד <a target="_blank" href="newRole.php">לחץ כאן</a></div>

                    <div class="field-name">עמותה</div>
                    <div class="field-value-w">
                        <div id="selectedAssociation" class="field-value noselect">בחר עמותה</div>                        
                    </div>
                    <div class="select-fields-menu-threshold">
                        <div class="select-fields-menu">
                            <?php echo $associations; ?>
                        </div>
                    </div>                    

                    <div class="field-name">טופס</div>
                    <div class="field-value-w">
                        <div id="selectedFile" class="field-value noselect">בחר טופס</div>
                    </div>
                    <div class="select-fields-menu-threshold">
                        <div class="select-fields-menu">
                            <?php echo $fileNames; ?>
                        </div>
                    </div>  
                    
                    <div id="export" class="button noselect animated-transition" style="margin-top: 30px;"><a>יצוא קבצים</a></div>

                </div>
            </div>
        </div>

    </body>
</html>