<?php
/**
 * /sso/login.php
 * -------------------------------------------------
 * SAML / SSO login entry point
 *
 * PURPOSE
 * -------
 * This endpoint initiates a Single Sign-On (SSO) login
 * by redirecting the user to the configured Identity
 * Provider (IdP) login URL.
 *
 * Any relevant query parameters (such as login keys or
 * return locations) are preserved and forwarded to
 * the IdP so they can be restored after authentication.
 */

// -------------------------------------------------
// Load SAML configuration
// -------------------------------------------------
require_once __DIR__ . '/config.php';

// -------------------------------------------------
// Preserve optional query parameters
// -------------------------------------------------
$params = [];

/**
 * Optional agent login key
 * Used to restrict access to the agent portal
 */
if (isset($_GET['key'])) {
    $params['key'] = $_GET['key'];
}

/**
 * Optional return location
 * Allows the application to redirect the user
 * back to their originally requested page
 */
if (isset($_GET['last_visited'])) {
    $params['last_visited'] = $_GET['last_visited'];
}

// -------------------------------------------------
// Build query string (if needed)
// -------------------------------------------------
$query = '';
if (!empty($params)) {
    $query = '?' . http_build_query($params);
}

// -------------------------------------------------
// Redirect user to Identity Provider login
// -------------------------------------------------
header('Location: ' . $SAML_LOGIN_URL . $query);
exit;

