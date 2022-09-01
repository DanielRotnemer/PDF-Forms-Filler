<?php

    header('Content-Type: text/html;charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Access-Control-Allow-Origin');
    header('Pragma: no-cache');
    date_default_timezone_set("Asia/Jerusalem");

    require_once('C:\xampp\htdocs\utils\utils.php');

    if (count($_COOKIE) === 0 || !isset($_COOKIE[session_name()]) || !file_exists('C:\xampp\tmp\sess_'.$_COOKIE[session_name()])) {
        session_id(UTILITIES::CreateSessionId());
    }
    else {
        session_id($_COOKIE[session_name()]);
    }
    session_start();

    if (!isset($_SESSION['Username']))
    {
        header("Location: http://$GLOBALS[SERVER_ADDRESS]/signin");
        exit;
    }

    $jsArrays = '<script type="text/javascript">';

    // SELECT ALL THE ROLES FROM THE DATABASE
    $pdo = UTILITIES::PDO_DB_Connection('roles');
    $stmt = $pdo->prepare("SELECT DISTINCT(RoleName) FROM roles_table");
    $stmt->execute();
    $rolesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $pdo = null;

    $jsArrays .= 'var roles = [';
    foreach ($rolesResult as $key => $value)
    {
        $delimiter = $key == 0 ? '' : ',';
        $jsArrays .= $delimiter.'"'.$value['RoleName'].'"';
    } 
    $jsArrays .= '];';

    // SELECT ALL THE DATABASE FIELDS FOR ASSOCIATIONS
    $pdo = UTILITIES::PDO_DB_Connection('associations');
    $stmt = $pdo->prepare("SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`='associations' AND `TABLE_NAME`='associations_table'");
    $stmt->execute();
    $associationsFieldsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $pdo = null;

    $jsArrays .= 'var associationsFields = [';
    foreach ($associationsFieldsResult as $key => $value)
    {
        $delimiter = $key == 0 ? '' : ',';
        $jsArrays .= $delimiter.'"'.$value['COLUMN_NAME'].'"';
    }
    $jsArrays .= '];';

    // SELECT ALL THE DATABASE FIELDS FOR WORKERS
    $pdo = UTILITIES::PDO_DB_Connection('workers');
    $stmt = $pdo->prepare("SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`='workers' AND `TABLE_NAME`='workers_table'");
    $stmt->execute();
    $workersFieldsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $pdo = null;

    $jsArrays .= 'var workersFields = [';
    foreach ($workersFieldsResult as $key => $value)
    {
        $delimiter = $key == 0 ? '' : ',';
        $jsArrays .= $delimiter.'"'.$value['COLUMN_NAME'].'"';
    }
    $jsArrays .= '];';

    $jsArrays .= '</script>';
?>

<html>
    <head>
        <title>העלאת טפסים</title>
        <link rel="stylesheet" href="upload.css?c=<?php echo time(); ?>"/>
        <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@2.10.377/build/pdf.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/pdf-lib@1.16.0/dist/pdf-lib.min.js"></script>
        <script type="text/javascript" src="upload.js?c=<?php echo time(); ?>"></script>        
    </head>
    <body>

        <div class="header">
            <div class="header-text">העלאת טפסים</div>
        </div>
        <input id="fileUpload" type="file" accept="application/pdf" style="display: none;"/>
        <div class="body-wrapper">
            <div class="upload-w">                
                <div class="btn-w">
                    <div id="upload" class="button noselect animated-transition"><a>העלאת קובץ</a></div>
                </div>
                <div class="btn-w" style="margin-top: 0;">
                    <div id="save" class="button noselect animated-transition"><a>שמור</a></div>
                </div>
                <div class="btn-w" style="margin-top: 0;">
                    <div class="text">ליצוא קבצים <a target="_blank" href="exportPages.php">לחץ כאן</a></div>
                    <div class="text" style="margin-top: 10px;">להוספת עמותה <a target="_blank" href="newAssociation.php">לחץ כאן</a></div>
                    <div class="text" style="margin-top: 10px;">להוספת עובד <a target="_blank" href="newWorker.php">לחץ כאן</a></div>
                    <div class="text" style="margin-top: 10px;">להוספת תפקיד <a target="_blank" href="newRole.php">לחץ כאן</a></div>
                </div>                
            </div>
            <div class="file-name">העלה טופס ע"מ ליעד לו שדות מבסיסי הנתונים</div>
            <div class="preview-w">
                <div class="selection-w">
                    <div class="inner-selection-w"></div>
                </div>
                <div class="file-preview"></div>
            </div>
        </div>

        <?php echo $jsArrays; ?>

    </body>
</html>