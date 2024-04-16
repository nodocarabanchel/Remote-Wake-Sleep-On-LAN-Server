<?php
/*
Remote Wake/Sleep-On-LAN Server
https://github.com/sciguy14/Remote-Wake-Sleep-On-LAN-Server
Original Author: Jeremy E. Blum (https://www.jeremyblum.com)
Security Edits By: Felix Ryan (https://www.felixrr.pro)
License: GPL v3 (http://www.gnu.org/licenses/gpl.html)
*/

// Include configuration file
require_once('config.php');

// Set headers to harden the HTTPS session
if ($USE_HTTPS && !isset($_SERVER['HTTPS'])) {
    header("Location: https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
    exit;
}

// Enable flushing
ini_set('implicit_flush', true);
ob_implicit_flush(true);
ob_end_flush();

// Set default computer (this is business logic so should be done last)
if (empty($_GET)) {
    header('Location: '. ($USE_HTTPS ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "?computer=0");
    exit;
} else {
    $selectedComputerIndex = isset($_GET['computer']) ? intval($_GET['computer']) : 0; // Default to 0 if not provided or invalid
}

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
    body, html {
        height: 100%;
        margin: 0;
        padding: 0;
        background: #f5f5f5;
    }
    body {
        background-image: url('bootstrap/background.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
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
            <h2 class="form-signin-heading">Remote Wake NPC</h2>
            <?php
                $approved = false;
                $wakeUp = false;
                $goToSleep = false;
                $checkCurrentStatus = false;

                if (isset($_POST['password'])) {
                    if (!is_null($APPROVED_HASH) && password_verify($_POST['password'], $APPROVED_HASH)) {
                        $approved = true;
                        switch ($_POST['submitbutton']) {
                            case "Wake Up!":
                                $wakeUp = true;
                                break;
                            case "Sleep!":
                                $goToSleep = true;
                                break;
                            case "Check Status":
                                $checkCurrentStatus = true;
                                break;
                        }
                    }
                }

                if ($approved) {
                    echo "<select name='computer' onchange='this.form.submit()'>";
                    foreach ($COMPUTER_NAME as $index => $name) {
                        echo "<option value='$index'" . ($selectedComputerIndex == $index ? " selected" : "") . ">$name</option>";
                    }
                    echo "</select>";
                } else {
                    echo "<p>Please log in.</p>";
                }

                if ($checkCurrentStatus) {
                    echo "<p>Approved. Please wait while the computer is queried for its current status...</p>";
                    $pingInfo = exec("ping -c 1 " . $COMPUTER_LOCAL_IP[$selectedComputerIndex]);
                    if (empty($pingInfo)) {
                        echo "<p style='color:#CC0000;'><b>" . $COMPUTER_NAME[$selectedComputerIndex] . " is currently offline.</b></p>";
                        echo "<form method='post'>";
                        echo "<input type='hidden' name='password' value='" . htmlspecialchars($_POST['password']) . "'>";
                        echo "<button type='submit' name='submitbutton' value='Wake Up!'>Wake Up!</button>";
                        echo "</form>";
                    } else {
                        echo "<p style='color:#00CC00;'><b>" . $COMPUTER_NAME[$selectedComputerIndex] . " is currently online.</b></p>";
                    }
                } elseif ($wakeUp) {
                    echo "<p>Selected Computer: $selectedComputerIndex</p>";
                    if (isset($selectedComputerIndex) && isset($COMPUTER_LOCAL_IP[$selectedComputerIndex])) {
                        $destinationAddress = $COMPUTER_LOCAL_IP[$selectedComputerIndex];
                    } else {
                        echo "<p>Error: Selected computer is not valid.</p>";
                        exit;
                    }
                    echo "<p>Approved. Sending WOL Command...</p>";
                    exec('wakeonlan ' . $COMPUTER_MAC[$selectedComputerIndex]);
                    echo "<p>Command Sent. Waiting for " . $COMPUTER_NAME[$selectedComputerIndex] . " to wake up...</p><p>";
                    $count = 1;
                    $down = true;
                    while ($count <= $MAX_PINGS && $down === true) {
                        echo "Ping " . $count . "...";
                        $pingInfo = exec("ping -c 1 " . $COMPUTER_LOCAL_IP[$selectedComputerIndex]);
                        $count++;
                        if (!empty($pingInfo)) {
                            $down = false;
                            echo "<span style='color:#00CC00;'><b>It's Alive!</b></span><br />";
                            echo "<p><a href='?computer=" . $selectedComputerIndex . "'>Return to the Wake/Sleep Control Home</a></p>";
                            $showForm = false;
                        } else {
                            echo "<span style='color:#CC0000;'><b>Still Down.</b></span><br />";
                        }
                        sleep($SLEEP_TIME);
                    }
                    if ($down === true) {
                        echo "<p style='color:#CC0000;'><b>FAILED!</b> " . $COMPUTER_NAME[$selectedComputerIndex] . " doesn't seem to be waking up... Try again?</p>";
                        echo "<p>(Or <a href='?computer=" . $selectedComputerIndex . "'>Return to the Wake/Sleep Control Home</a>.)</p>";
                    }
                } elseif ($goToSleep) {
                    echo "<p>Approved. Sending Sleep Command...</p>";
                    // Implement sleep command logic here
                    $sleepCommand = "ssh -o StrictHostKeyChecking=no " . $COMPUTER_USER[$selectedComputerIndex] . "@" . $COMPUTER_LOCAL_IP[$selectedComputerIndex] . " 'poweroff'";
                    exec($sleepCommand, $output, $returnVar);
                    if ($returnVar == 0) {
                        echo "<p style='color:#00CC00;'><b>Sleep command sent successfully.</b></p>";
                    } else {
                        echo "<p style='color:#CC0000;'><b>Failed to send sleep command.</b></p>";
                    }
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
