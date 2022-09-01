<?php

    header('Content-Type: text/html;charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
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

    $error = '';
    if (isset($_POST['newRole']))
    {
        foreach ($_POST as $key => $value)
        {
            if (empty($_POST[$key]) && $key != 'newRole') {
                $error = '<div class="error">חובה למלא את כל השדות הנדרשים</div>';
            }
        }

        if ($error == '')
        {
            // INSERT THE ROLE OF THIS WORKER
            $pdo = UTILITIES::PDO_DB_Connection('roles');
            $stmt = $pdo->prepare("INSERT INTO roles_table (WorkerId,RoleName,AssociationId) VALUES (?,?,?)");
            $stmt->execute([$_POST['selectedWorker'], $_POST['role'], $_POST['selectedAssociation']]);
            $pdo = null;
        }        
    }

    // SELECT ALL THE ASSOCIATIONS FROM THE DATABASE    
    $pdo = UTILITIES::PDO_DB_Connection('associations');
    $stmt = $pdo->prepare("SELECT AssociationId,AssociationName,AssociationNumber FROM associations_table");
    $stmt->execute();
    $associationsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $pdo = null;

    // SELECT ALL THE WORKERS FROM THE DATABASE    
    $pdo = UTILITIES::PDO_DB_Connection('workers');
    $stmt = $pdo->prepare("SELECT WorkerId,IdNumber,FirstName,LastName FROM workers_table");
    $stmt->execute();
    $workersResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $pdo = null;

    $jsArrays = 'var associations = [';
    
    $document =
    '<div class="body-wrapper">
        <div class="header">
            <div class="header-text">הוספת עובד</div>
        </div>
        <div class="form-wrapper">
            <div class="title">הוספת עובד</div>
            <form method="post" autocomplete="off" enctype="multipart/form-data" accept-charset="UTF-8">
                <div class="field-w">
                    <div class="field-name">תפקיד</div>
                    <input autocomplete="off" id="role" name="role" type="text" class="field-input"/>
                </div>
                <div class="field-w">
                    <div class="field-name">עמותה</div>
                    <select id="association" name="association" class="field-input">';
                    foreach ($associationsResult as $key => $value)
                    {
                        $delimiter = $key == 0 ? '' : ',';
                        $document .= '<option>'.$value['AssociationName'].' - '.$value['AssociationNumber'].'</option>';
                        $jsArrays .= $delimiter.'"'.$value['AssociationId'].'"';
                    }
                    $jsArrays .= '];';
                    $document .=
                    '</select>
                </div>
                <div class="field-w">
                    <div class="field-name">עובד</div>
                    <select id="worker" name="worker" class="field-input">';
                    $jsArrays .= 'var workers = [';
                    foreach ($workersResult as $key => $value)
                    {
                        $delimiter = $key == 0 ? '' : ',';
                        $document .= '<option>'.$value['FirstName'].' '.$value['LastName'].' - '.$value['IdNumber'].'</option>';
                        $jsArrays .= $delimiter.'"'.$value['WorkerId'].'"';
                    }
                    $jsArrays .= '];';
                    $document .=
                    '</select>
                </div>
                '.$error.'
                <button id="newRole" name="newRole" class="add">הוספה</button>
                <input type="hidden" id="selectedAssociation" name="selectedAssociation"/>
                <input type="hidden" id="selectedWorker" name="selectedWorker"/>
            </form>
        </div>
    </div>';

?>

<html>
    <head>
        <title>הוספת תפקיד</title>
        <link rel="stylesheet" href="http://<?php echo $GLOBALS['SERVER_ADDRESS']; ?>/newItem.css?c=<?php echo time(); ?>s"/>
        <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script typee="text/javascript" src="http://<?php echo $GLOBALS['SERVER_ADDRESS']; ?>/newItem.js?c=<?php echo time(); ?>"></script>
    </head>
    <body>
        <?php echo $document; ?>
        <?php echo '<script>'.$jsArrays.'</script>'; ?>        
        <script>
            $(window).on("load", function()
            {
                $("#selectedAssociation").val(associations[0]);
                $("#selectedWorker").val(workers[0]);

                $("#association").on("change", function()
                {
                    $("#selectedAssociation").val(associations[$(this)[0].selectedIndex]);
                });

                $("#worker").on("change", function()
                {
                    $("#selectedWorker").val(workers[$(this)[0].selectedIndex]);
                });
            });            
        </script>
    </body>
</html>