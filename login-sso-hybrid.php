<?php

// Unified login (Agent + Client)

// Enforce CSP
header("Content-Security-Policy: default-src 'self'");

// ----------------------------------------------------
// Bootstrap
// ----------------------------------------------------

if (!file_exists('config.php')) {
    header("Location: /setup");
    exit();
}

require_once "config.php";
require_once "functions.php";
require_once "plugins/totp/totp.php";

// Sessions
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

if (!isset($config_enable_setup) || $config_enable_setup == 1) {
    header("Location: /setup");
    exit();
}

if (
    $config_https_only &&
    (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') &&
    (!isset($_SERVER['HTTP_X_FORWARDED_PROTO']) || $_SERVER['HTTP_X_FORWARDED_PROTO'] !== 'https')
) {
    echo "Login is restricted as ITFlow defaults to HTTPS-only.";
    exit;
}

// ----------------------------------------------------
// Timezone
// ----------------------------------------------------
require_once "includes/inc_set_timezone.php";

// ----------------------------------------------------
// Company settings
// ----------------------------------------------------

$sql_settings = mysqli_query($mysqli, "
    SELECT settings.*, companies.company_name, companies.company_logo
    FROM settings
    LEFT JOIN companies ON settings.company_id = companies.company_id
    WHERE settings.company_id = 1
");
$row = mysqli_fetch_array($sql_settings);

$company_name                 = $row['company_name'];
$company_logo                 = $row['company_logo'];
$config_login_message         = nullable_htmlentities($row['config_login_message']);
$config_client_portal_enable  = intval($row['config_client_portal_enable']);
$azure_client_id              = $row['config_azure_client_id'] ?? null;

// ----------------------------------------------------
// Login form vars
// ----------------------------------------------------

$response = null;
$email    = '';

// ----------------------------------------------------
// (OPTIONAL) normal login POST handling
// ----------------------------------------------------
// You already have this logic elsewhere — keep it as-is
// or plug it back in here if needed.
// ----------------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= nullable_htmlentities($company_name) ?> | Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">

    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="plugins/adminlte/css/adminlte.min.css">
</head>

<body class="hold-transition login-page">

<div class="login-box">

    <div class="login-logo">
        <?php if (!empty($company_logo)) { ?>
            <img src="uploads/settings/<?= $company_logo ?>" class="img-fluid" alt="Logo">
        <?php } else { ?>
            <b>IT</b>Flow
        <?php } ?>
    </div>

    <div class="card">
        <div class="card-body login-card-body">

            <?php if (!empty($config_login_message)) { ?>
                <p class="login-box-msg"><?= nl2br($config_login_message) ?></p>
            <?php } ?>

            <?php if (!empty($response)) { ?>
                <?= $response ?>
            <?php } ?>

            <!-- ========================= -->
            <!-- NORMAL LOGIN -->
            <!-- ========================= -->

            <form method="post">

                <div class="input-group mb-3">
                    <input type="email"
                           class="form-control"
                           name="email"
                           placeholder="Email"
                           required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <input type="password"
                           class="form-control"
                           name="password"
                           placeholder="Password"
                           required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>

                <button type="submit"
                        name="login"
                        class="btn btn-primary btn-block mb-3">
                    Sign in
                </button>

            </form>

            <!-- ========================= -->
            <!-- SSO LOGIN -->
            <!-- ========================= -->

            <div class="text-center">
                <a href="/sso/login.php" class="btn btn-secondary btn-block">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Login with SSO
                </a>
            </div>

            <!-- ========================= -->
            <!-- CLIENT PORTAL -->
            <!-- ========================= -->

            <?php if ($config_client_portal_enable == 1) { ?>
                <hr>
                <div class="text-center">
                    <a href="/client/index.php">Client Portal</a>
                </div>
            <?php } ?>

        </div>
    </div>
</div>

</body>
</html>

