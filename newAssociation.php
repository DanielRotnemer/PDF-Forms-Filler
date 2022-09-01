<?php

    header('Content-Type: text/html;charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    date_default_timezone_set("Asia/Jerusalem");

    require_once('utils/utils.php');

    if (count($_COOKIE) === 0 || !isset($_COOKIE[session_name()]) || !file_exists('C:\xampp\tmp\sess_'.$_COOKIE[session_name()])) {
        session_id(UTILITIES::CreateSessionId());
    }
    else {
        session_id($_COOKIE[session_name()]);
    }
    session_start();

    $error = '';
    if (isset($_POST['newAssociation']))
    {
        foreach ($_POST as $key => $value)
        {
            if (empty($_POST[$key]) && $key != 'newAssociation') {
                $error = '<div class="error">חובה למלא את כל השדות הנדרשים</div>';
            }
        }

        if ($error == '')
        {
            // SELECT ALL THE ROLES FROM THE DATABASE
            $pdo = UTILITIES::PDO_DB_Connection('associations');
            $stmt = $pdo->prepare("INSERT INTO associations_table (AssociationName,Street,HouseNumber,City,PhoneNumber,AssociationNumber,DeductionFileId,Activity,Goals,CreationDate,InstitutionCode,BankName,BankBranch,BankAccountNumber) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$_POST['assocName'], $_POST['street'], $_POST['houseNumber'], $_POST['city'], $_POST['phone'], $_POST['associationNumber'], $_POST['deductionField'], $_POST['activity'], $_POST['goals'], $_POST['startDate'], $_POST['associationCode'], $_POST['bankName'], $_POST['branch'], $_POST['bankAccNumber']]);
            $pdo = null;
        }        
    }
        
    $document =
    '<div class="body-wrapper">
        <div class="header">
            <div class="header-text">הוספת עמותה</div>
        </div>
        <div class="form-wrapper">
            <div class="title">הוספת עמותה</div>
            <form method="post" autocomplete="off" enctype="multipart/form-data" accept-charset="UTF-8">
                <div class="field-w">
                    <div class="field-name">שם&nbsp;העמותה</div>
                    <input autocomplete="off" id="assocName" name="assocName" type="text" class="field-input"/>
                </div>
                <div class="field-w">
                    <div class="field-name">רחוב</div>
                    <input autocomplete="off" id="street" name="street" type="text" class="field-input"/>
                </div>
                <div class="field-w">
                    <div class="field-name">מספר&nbsp;בית</div>
                    <input autocomplete="off" id="houseNumber" name="houseNumber" type="text" class="field-input"/>
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
                    <div class="field-name">מספר&nbsp;עמותה</div>
                    <input autocomplete="off" id="associationNumber" name="associationNumber" type="text" class="field-input"/>
                </div>
                <div class="field-w">
                    <div class="field-name">מספר&nbsp;תיק&nbsp;ניכויים</div>
                    <input autocomplete="off" id="deductionField" name="deductionField" type="text" class="field-input"/>
                </div>
                <div class="long-field-name">פעילות</div>
                <textarea id="activity" name="activity" class="long-field-value"></textarea>
                <div class="long-field-name">מטרות</div>
                <textarea id="goals" name="goals" class="long-field-value"></textarea>
                <div class="field-w">
                    <div class="field-name">תאריך&nbsp;התחלה</div>
                    <input autocomplete="off" id="startDate" name="startDate" type="date" class="field-input"/>
                </div>
                <div class="field-w">
                    <div class="field-name">קוד&nbsp;עמותה</div>
                    <input autocomplete="off" id="associationCode" name="associationCode" type="text" class="field-input"/>
                </div>
                <div class="field-w">
                    <div class="field-name">שם&nbsp;בנק</div>
                    <input autocomplete="off" id="bankName" name="bankName" type="text" class="field-input"/>
                </div>
                <div class="field-w">
                    <div class="field-name">סניף&nbsp;בנק</div>
                    <input autocomplete="off" id="branch" name="branch" type="text" class="field-input"/>
                </div>
                <div class="field-w">
                    <div class="field-name">מספר&nbsp;חשבון&nbsp;בנק</div>
                    <input autocomplete="off" id="bankAccNumber" name="bankAccNumber" type="text" class="field-input"/>
                </div>
                '.$error.'
                <button id="newAssociation" name="newAssociation" class="add">הוספה</button>
            </form>
        </div>
    </div>';

?>

<html>
    <head>
        <title>הוספת עמותה</title>
        <link rel="stylesheet" href="http://<?php echo $GLOBALS['SERVER_ADDRESS']; ?>/newItem.css?c=<?php echo time(); ?>s"/>
        <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script typee="text/javascript" src="http://<?php echo $GLOBALS['SERVER_ADDRESS']; ?>/newItem.js?c=<?php echo time(); ?>"></script>
    </head>
    <body>
        <?php echo $document; ?>
    </body>
</html>