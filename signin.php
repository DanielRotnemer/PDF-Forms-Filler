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

    if (isset($_POST['login']))
    {
        // SELECT ALL THE ROLES FROM THE DATABASE
        $pdo = UTILITIES::PDO_DB_Connection('managers');
        $stmt = $pdo->prepare("SELECT Username,Password FROM managers_table WHERE BINARY Username=? AND Password=?");
        $stmt->execute([$_POST['username'], $_POST['password']]);
        $loginResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        if (count($loginResult) > 0) 
        {
            $_SESSION['Username'] = $loginResult[0]['Username']; 
            header("Location: http://$GLOBALS[SERVER_ADDRESS]/exportPages.php?c=".$_SESSION['Username']);
            exit;
        }   
        else 
        {
            $error = '<div class="error">פרטי זיהוי שגויים</div>';
        }     
    }

    $document = '';
    if (isset($_SESSION['Username']))
    {
        $document = 
        '<div class="body-wrapper">
            <div class="form-wrapper">
                <form method="post" autocomplete="off" enctype="multipart/form-data" accept-charset="UTF-8">
                    <div class="text">הנך מחובר למערכת - <a href="upload.php">להעלאת קבצים</a></div>
                    <div class="text" style="margin-top: 20px;"><a href="logout.php">התנתקות</a></div>
                </form>
            </div>
        </div>';
    }   
    else 
    {
        $document =
        '<div class="body-wrapper">
            <div class="form-wrapper">
                <div class="title">כניסה למערכת</div>
                <form method="post" autocomplete="off" enctype="multipart/form-data" accept-charset="UTF-8">
                    <div class="field-w">
                        <div class="field-name">שם&nbsp;משתמש</div>
                        <input autocomplete="off" id="username" name="username" type="text" class="field-input"/>
                    </div>
                    <div class="field-w">
                        <div class="field-name">סיסמא</div>
                        <input autocomplete="off" id="password" name="password" type="password" class="field-input"/>
                    </div>
                    '.$error.'
                    <button id="login" name="login" class="login">כניסה</button>
                </form>
            </div>
        </div>';
    }

?>

<html>
    <head>
        <title>התחברות</title>
        <link rel="stylesheet" href="http://<?php echo $GLOBALS['SERVER_ADDRESS']; ?>/signin.css?c=<?php echo time(); ?>s"/>
        <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script typee="text/javascript" src="http://<?php echo $GLOBALS['SERVER_ADDRESS']; ?>/signin.js?c=<?php echo time(); ?>"></script>
    </head>
    <body>
        <?php echo $document; ?>
    </body>
</html>