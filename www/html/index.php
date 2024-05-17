<?php
/*
Remote Wake/Sleep-On-LAN Server
https://github.com/sciguy14/Remote-Wake-Sleep-On-LAN-Server
Original Author: Jeremy E. Blum (https://www.jeremyblum.com)
Security Edits By: Felix Ryan (https://www.felixrr.pro)
License: GPL v3 (http://www.gnu.org/licenses/gpl.html)
*/

session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
]);

// Include configuration file
require_once('config.php');

// Define an access control constant
define('ACCESS_ALLOWED', true);

// Set headers to harden the HTTPS session
if ($USE_HTTPS && !isset($_SERVER['HTTPS'])) {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}
header('Content-Type: text/html; charset=utf-8');
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: no-referrer-when-downgrade');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline';");

// CSRF protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check user inputs and determine the action
$approved = $_SESSION['approved'] ?? false;
$status_message = "Ready"; // Default status message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        if (isset($_POST['password']) && password_verify($_POST['password'], $APPROVED_HASH)) {
            $_SESSION['approved'] = true; // Set session approved if password is verified.
            session_regenerate_id(true); // Regenerate session ID upon login
            $approved = true;
            $status_message = "Login successful!";
        } else {
            $login_error = "Invalid passphrase. Please try again.";
        }

        if ($approved && isset($_POST['submitbutton'])) {
            // Handle operations
            $selectedComputerIndex = $_POST['computer'] ?? 0;
            $action = $_POST['submitbutton'];
            $status_message = handleOperations($selectedComputerIndex, $action);
        }
    } else {
        $login_error = "CSRF token mismatch.";
    }
}

function handleOperations($computerId, $action) {
    global $COMPUTER_MAC, $COMPUTER_LOCAL_IP, $COMPUTER_NAME;
    switch ($action) {
        case "Wake Up!":
            $mac = $COMPUTER_MAC[$computerId];
            exec("wakeonlan $mac");
            return "Command Sent. Waiting for " . $COMPUTER_NAME[$computerId] . " to wake up...";
        case "Sleep!":
            $ip = $COMPUTER_LOCAL_IP[$computerId];
            exec("ssh -o StrictHostKeyChecking=no pi@$ip 'sudo poweroff'");
            return "Sleep command sent to " . $COMPUTER_NAME[$computerId] . ".";
        case "Check Status":
            $ip = $COMPUTER_LOCAL_IP[$computerId];
            $result = exec("ping -c 1 $ip");
            if (empty($result)) {
                return $COMPUTER_NAME[$computerId] . " is offline.";
            } else {
                return $COMPUTER_NAME[$computerId] . " is online.";
            }
    }
    return "Invalid action specified.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Remote Wake/Sleep-On-LAN</title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A utility for remotely waking/sleeping a computer via a Raspberry Pi">
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
    .status-message {
        position: absolute;
        top: 10px;
        left: 10px;
        color: #000;
        background-color: rgba(255,255,255,0.8);
        padding: 5px 10px;
        border-radius: 5px;
    }
    </style>
</head>
<body>
    <div class="status-message"><?php echo $status_message; ?></div>
    <div class="container">
        <form class="form-signin" method="post">
            <h2 class="form-signin-heading">Remote Wake NPC</h2>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <?php if ($approved): ?>
                <div>Welcome, you are logged in!</div>
                <select name="computer" onchange='this.form.submit()'>
                    <?php foreach ($COMPUTER_NAME as $index => $name): ?>
                        <option value='<?php echo $index; ?>' <?php echo ($selectedComputerIndex == $index ? "selected" : ""); ?>>
                            <?php echo htmlspecialchars($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="submitbutton" value="Wake Up!">Wake Up!</button>
                <button type="submit" name="submitbutton" value="Sleep!">Sleep!</button>
                <button type="submit" name="submitbutton" value="Check Status">Check Status</button>
            <?php else: ?>
                <?php if (isset($login_error)): ?>
                    <p><?php echo $login_error; ?></p>
                <?php endif; ?>
                <label for="password">Password:</label>
                <input type="password" name="password" class="input-block-level" placeholder="Enter Passphrase" required>
                <button class="btn btn-large btn-primary" type="submit" name="submitbutton" value="Check Status">Check Status</button>
            <?php endif; ?>
        </form>
    </div>
    <script src="<?php echo $BOOTSTRAP_LOCATION_PREFIX; ?>bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
