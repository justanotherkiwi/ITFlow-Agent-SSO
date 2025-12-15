<?php
/**
 * /sso/slo.php
 * -------------------------------------------------
 * Local logout handler for SAML / SSO authentication
 *
 * PURPOSE
 * -------
 * This endpoint performs a **local application logout** for users
 * authenticated via Single Sign-On (SSO).
 *
 * It clears the active PHP session, removes the session cookie,
 * and redirects the user to the login page.
 *
 * IMPORTANT
 * ---------
 * This is a **local logout only**.
 * It does NOT initiate a SAML Single Logout (SLO) request with
 * the Identity Provider (IdP).
 *
 * The user may still have an active IdP session and could be
 * re-authenticated automatically on the next SSO login attempt.
 */

// -------------------------------------------------
// Load application configuration
// -------------------------------------------------
require_once __DIR__ . '/../config.php';

// -------------------------------------------------
// Ensure a session exists
// -------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -------------------------------------------------
// Clear all session variables
// -------------------------------------------------
$_SESSION = [];

// -------------------------------------------------
// Remove session cookie (if cookies are in use)
// -------------------------------------------------
if (ini_get('session.use_cookies')) {

    // Retrieve current session cookie parameters
    $params = session_get_cookie_params();

    // Expire the session cookie in the browser
    setcookie(
        session_name(),          // Session cookie name
        '',                       // Empty value
        time() - 42000,           // Expired timestamp
        $params['path'],          // Cookie path
        $params['domain'],        // Cookie domain
        $params['secure'],        // Secure flag
        $params['httponly']       // HTTP-only flag
    );
}

// -------------------------------------------------
// Destroy the server-side session
// -------------------------------------------------
session_destroy();

// -------------------------------------------------
// Redirect user to login page
// -------------------------------------------------
header('Location: /login.php');
exit;

