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
    if (isset($_POST['newWorker']))
    {
        foreach ($_POST as $key => $value)
        {
            if (empty($_POST[$key]) && $key != 'newWorker') {
                $error = '<div class="error">חובה למלא את כל השדות הנדרשים</div>';
            }
        }

        if ($error == '')
        {
            // INSERT THE WORKER
            $pdo = UTILITIES::PDO_DB_Connection('workers');
            $stmt = $pdo->prepare("INSERT INTO workers_table (IdNumber,FirstName,LastName,Street,HouseNumber,City,PhoneNumber,MobileNumber) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$_POST['idNumber'], $_POST['firstName'], $_POST['lastName'], $_POST['street'], $_POST['houseNumber'], $_POST['city'], $_POST['phone'], $_POST['mobile']]);
            $pdo = null;
        }        
    }
    
    $document =
    '<div class="body-wrapper">
        <div class="header">
            <div class="header-text">הוספת עובד</div>
        </div>
        <div class="form-wrapper">
            <div class="title">הוספת עובד</div>
            <form method="post" autocomplete="off" enctype="multipart/form-data" accept-charset="UTF-8">
                <div class="field-w">
                    <div class="field-name">שם&nbsp;פרטי</div>
                    <input autocomplete="off" id="firstName" name="firstName" type="text" class="field-input"/>
                </div>
                <div class="field-w">
                    <div class="field-name">שם&nbsp;משפחה</div>
                    <input autocomplete="off" id="lastName" name="lastName" type="text" class="field-input"/>
                </div>
                <div class="field-w">
                    <div class="field-name">מספר&nbsp;זהות</div>
                    <input autocomplete="off" id="idNumber" name="idNumber" type="text" class="field-input"/>
                </div>
                <div class="field-w">
                    <div class="field-name">עיר</div>
                    <input autocomplete="off" id="city" name="city" type="text" class="field-input"/>
                </div>
                <div class="field-w">
                    <div class="field-name">טלפון</div>
                    <input autocomplete="off" id="phone" name="phone" type="text" class="field-input"/>
                </div>
                <div class="field-w">
                    <div class="field-name">מספר&nbsp;נייד</div>
                    <input autocomplete="off" id="mobile" name="mobile" type="text" class="field-input"/>
                </div>
                <div class="field-w">
                    <div class="field-name">מספר&nbsp;בית</div>
                    <input autocomplete="off" id="houseNumber" name="houseNumber" type="text" class="field-input"/>
                </div>
                <div class="field-w">
                    <div class="field-name">רחוב</div>
                    <input autocomplete="off" id="street" name="street" type="text" class="field-input"/>
                </div>
                '.$error.'
                <button id="newWorker" name="newWorker" class="add">הוספה</button>
            </form>
        </div>
    </div>';

?>

<html>
    <head>
        <title>הוספת עובד</title>
        <link rel="stylesheet" href="http://<?php echo $GLOBALS['SERVER_ADDRESS']; ?>/newItem.css?c=<?php echo time(); ?>s"/>
        <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script typee="text/javascript" src="http://<?php echo $GLOBALS['SERVER_ADDRESS']; ?>/newItem.js?c=<?php echo time(); ?>"></script>
    </head>
    <body>
        <?php echo $document; ?>
    </body>
</html>