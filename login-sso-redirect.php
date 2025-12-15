<?php

// Unified login (Agent + Client) using one email & password

// Enforce a Content Security Policy for security against cross-site scripting
header("Content-Security-Policy: default-src 'self'");

// ----------------------------------------------------
// Bootstrap
// ----------------------------------------------------

// Check if the config.php file exists
if (!file_exists('config.php')) {
    header("Location: /setup");
    exit();
}

require_once "config.php";
require_once "functions.php";
require_once "plugins/totp/totp.php";

// Sessions & cookies
if (session_status() === PHP_SESSION_NONE) {
    ini_set("session.cookie_httponly", true);

    if ($config_https_only || !isset($config_https_only)) {
        ini_set("session.cookie_secure", true);
    }

    session_start();
}

// ----------------------------------------------------
// Setup + HTTPS enforcement
// ----------------------------------------------------

// Check if setup mode is enabled or the variable is missing
if (!isset($config_enable_setup) || $config_enable_setup == 1) {
    header("Location: /setup");
    exit();
}

// Check if HTTPS-only is enforced
if (
    $config_https_only &&
    (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') &&
    (!isset($_SERVER['HTTP_X_FORWARDED_PROTO']) || $_SERVER['HTTP_X_FORWARDED_PROTO'] !== 'https')
) {
    echo "Login is restricted as ITFlow defaults to HTTPS-only for enhanced security.";
    exit;
}

// ----------------------------------------------------
// AUTO-REDIRECT TECHNICIANS TO SSO
// ----------------------------------------------------

if (empty($_SESSION['logged']) || $_SESSION['logged'] !== true) {

    // Respect agent login key if enabled
    $agent_allowed = true;
    if ($config_login_key_required) {
        $agent_allowed = (
            isset($_GET['key']) &&
            $_GET['key'] === $config_login_key_secret
        );
    }

    if ($agent_allowed) {

        // Preserve query params
        $params = [];
        if (isset($_GET['key'])) {
            $params['key'] = $_GET['key'];
        }
        if (isset($_GET['last_visited'])) {
            $params['last_visited'] = $_GET['last_visited'];
        }

        $query = $params ? '?' . http_build_query($params) : '';

        header('Location: /sso/login.php' . $query);
        exit();
    }
}

// ----------------------------------------------------
// Legacy login logic (kept for break-glass / clients)
// ----------------------------------------------------

// Set Timezone after session_start
require_once "includes/inc_set_timezone.php";

// IP & User Agent for logging
$session_ip = sanitizeInput(getIP());
$session_user_agent = sanitizeInput($_SERVER['HTTP_USER_AGENT'] ?? '');

// Block brute force password attacks
$row = mysqli_fetch_assoc(mysqli_query(
    $mysqli,
    "SELECT COUNT(log_id) AS failed_login_count
     FROM logs
     WHERE log_ip = '$session_ip'
       AND log_type = 'Login'
       AND log_action = 'Failed'
       AND log_created_at > (NOW() - INTERVAL 10 MINUTE)"
));
$failed_login_count = intval($row['failed_login_count']);

if ($failed_login_count >= 15) {
    logAction("Login", "Blocked", "$session_ip blocked due to IP lockout");
    header("HTTP/1.1 429 Too Many Requests");
    exit("<h2>$config_app_name</h2>Your IP address has been blocked due to repeated failed login attempts.");
}

// ----------------------------------------------------
// Load company settings
// ----------------------------------------------------

$sql_settings = mysqli_query($mysqli, "
    SELECT settings.*, companies.company_name, companies.company_logo
    FROM settings
    LEFT JOIN companies ON settings.company_id = companies.company_id
    WHERE settings.company_id = 1
");
$row = mysqli_fetch_array($sql_settings);

// Company info
$company_name          = $row['company_name'];
$company_logo          = $row['company_logo'];
$config_start_page     = nullable_htmlentities($row['config_start_page']);
$config_login_message  = nullable_htmlentities($row['config_login_message']);

// Client portal
$config_client_portal_enable     = intval($row['config_client_portal_enable']);
$config_login_remember_me_expire = intval($row['config_login_remember_me_expire']);
$azure_client_id                 = $row['config_azure_client_id'] ?? null;

// ----------------------------------------------------
// Legacy form variables (only used if SSO bypassed)
// ----------------------------------------------------

$response         = null;
$token_field      = null;
$show_role_choice = false;
$email            = '';
$password         = '';

// ----------------------------------------------------
// HTML (rarely reached now — SSO-first)
// ----------------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?=nullable_htmlentities($company_name)?> | Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">

    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="plugins/adminlte/css/adminlte.min.css">
</head>

<body class="hold-transition login-page">

<div class="login-box">
    <div class="login-logo">
        <?php if (!empty($company_logo)) { ?>
            <img src="uploads/settings/<?=$company_logo?>" class="img-fluid" alt="Logo">
        <?php } else { ?>
            <b>IT</b>Flow
        <?php } ?>
    </div>

    <div class="card">
        <div class="card-body login-card-body">

            <p class="login-box-msg">
                Redirecting to secure sign-in…
            </p>

            <div class="text-center">
                <a href="/sso/login.php" class="btn btn-primary btn-block">
                    Continue to Login
                </a>
            </div>

            <?php if ($config_client_portal_enable == 1): ?>
                <hr>
                <div class="text-center">
                    <a href="client/index.php">Client Portal</a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>

