<?php
/*
Remote Wake/Sleep-On-LAN Server
https://github.com/sciguy14/Remote-Wake-Sleep-On-LAN-Server
Original Author: Jeremy E. Blum (https://www.jeremyblum.com)
Security Edits By: Felix Ryan (https://www.felixrr.pro)
License: GPL v3 (http://www.gnu.org/licenses/gpl.html)
*/

//You should not need to edit this file. Adjust Parameters in the config file:
require_once('config.php');

//set headers that harden the HTTPS session
if ($USE_HTTPS)
{
   header("Strict-Transport-Security: max-age=7776000"); //HSTS headers set for 90 days
}

// Enable flushing
ini_set('implicit_flush', true);
ob_implicit_flush(true);
ob_end_flush();

//Set the correct protocol
if ($USE_HTTPS && !$_SERVER['HTTPS'])
{
   header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
   exit;
}

//Set default computer (this is business logic so should be done last)
if (empty($_GET))
{
   header('Location: '. ($USE_HTTPS ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "?computer=0");
   exit;
}
else
   $_GET['computer'] = preg_replace("/[^0-9,.]/", "", $_GET['computer']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Remote Wake/Sleep-On-LAN</title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A utility for remotely waking/sleeping a Windows computer via a Raspberry Pi">
    <meta name="author" content="Jeremy Blum">

    <!-- Styles -->
    <link href="<?php echo $BOOTSTRAP_LOCATION_PREFIX; ?>bootstrap/css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
        body {
            padding-top: 40px !important;
            padding-bottom: 40px;
            background-color: #f5f5f5; /* You might want to remove or change this if it conflicts with your image */
            background-image: url('<?php echo $BOOTSTRAP_LOCATION_PREFIX; ?>images/background.jpg'); /* Add your image URL here */
            background-size: cover; /* Cover ensures the background image covers the entire body */
            background-position: center; /* Center the background image */
        }
        .form-signin {
            max-width: 600px;
            padding: 19px 29px 29px;
            margin: 0 auto 20px;
            background-color: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 5px;
            box-shadow: 0 1px 2px rgba(0,0,0,.05);
        }
    </style>
    <link href="<?php echo $BOOTSTRAP_LOCATION_PREFIX; ?>bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <form class="form-signin" method="post">
            <h2 class="form-signin-heading">Remote Wake/Sleep-On-LAN</h2>
            <?php
                $approved = false;
                $wake_up = false;
                $go_to_sleep = false;
                $check_current_status = false;

                if (isset($_POST['password'])) {
                    if (!is_null($APPROVED_HASH) && password_verify($_POST['password'], $APPROVED_HASH)) {
                        $approved = true;
                        switch ($_POST['submitbutton']) {
                            case "Wake Up!":
                                $wake_up = true;
                                break;
                            case "Sleep!":
                                $go_to_sleep = true;
                                break;
                            case "Check Status":
                                $check_current_status = true;
                                break;
                        }
                    }
                }

                if ($approved) {
                    echo "<select name='computer' onchange='this.form.submit()'>";
                    foreach ($COMPUTER_NAME as $index => $name) {
                        echo "<option value='$index'" . ($selectedComputer == $index ? " selected" : "") . ">$name</option>";
                    }
                    echo "</select>";
                } else {
                    echo "<p>Please log in to see the computer options.</p>";
                }

                if ($check_current_status) {
                    echo "<p>Approved. Please wait while the computer is queried for its current status...</p>";
                    // Implement status check logic here
                } elseif ($wake_up) {
                    echo "<p>Approved. Sending WOL Command...</p>";
                    // Implement wake command logic here
                } elseif ($go_to_sleep) {
                    echo "<p>Approved. Sending Sleep Command...</p>";
                    // Implement sleep command logic here
                } elseif (isset($_POST['submitbutton'])) {
                    echo "<p style='color:#CC0000;'><b>Invalid Passphrase. Request Denied.</b></p>";
                }

                if (!isset($_POST['submitbutton']) || !$approved) {
                    echo '<input type="password" name="password" class="input-block-level" placeholder="Enter Passphrase">';
                    echo '<button class="btn btn-large btn-primary" type="submit" name="submitbutton" value="Check Status">Check Status</button>';
                }
            ?>
        </form>
    </div>
    <script src="<?php echo $BOOTSTRAP_LOCATION_PREFIX; ?>bootstrap/js/bootstrap.min.js"></script>
</body>
</html>