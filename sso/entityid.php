<?php
/**
 * /sso/entityid.php
 * -------------------------------------------------
 * Service Provider (SP) metadata endpoint
 *
 * PURPOSE
 * -------
 * This endpoint generates and exposes SAML metadata
 * for this application acting as a Service Provider.
 *
 * The metadata is consumed by an external Identity
 * Provider (IdP) during SSO configuration and contains
 * information such as:
 *   - Entity ID
 *   - Assertion Consumer Service (ACS) URL
 *   - Single Logout Service (SLO) URL
 *   - Supported bindings
 *
 * This file outputs XML and should be referenced
 * by the IdP during SAML setup.
 */

// -------------------------------------------------
// Load application configuration
// -------------------------------------------------
require_once __DIR__ . '/../config.php';

// -------------------------------------------------
// Enforce HTTPS if required by application settings
// -------------------------------------------------
if (
    $config_https_only &&
    (
        !isset($_SERVER['HTTPS']) ||
        $_SERVER['HTTPS'] !== 'on'
    ) &&
    (
        !isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ||
        $_SERVER['HTTP_X_FORWARDED_PROTO'] !== 'https'
    )
) {
    http_response_code(400);
    exit('HTTPS is required by application configuration');
}

// -------------------------------------------------
// Load SAML library (OneLogin toolkit)
// -------------------------------------------------
require_once __DIR__ . '/../vendor/autoload.php';

// -------------------------------------------------
// Load SAML settings
// -------------------------------------------------
$settings = require __DIR__ . '/saml_settings.php';

// -------------------------------------------------
// Generate SP metadata
// -------------------------------------------------
$oneLoginSettings = new OneLogin\Saml2\Settings($settings, true);
$metadata         = $oneLoginSettings->getSPMetadata();
$errors           = $oneLoginSettings->validateMetadata($metadata);

// -------------------------------------------------
// Validate metadata before output
// -------------------------------------------------
if (!empty($errors)) {
    http_response_code(500);
    exit('Invalid SP metadata: ' . implode(', ', $errors));
}

// -------------------------------------------------
// Output metadata as XML
// -------------------------------------------------
header('Content-Type: application/samlmetadata+xml');
echo $metadata;

