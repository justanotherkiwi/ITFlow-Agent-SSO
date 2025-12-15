<?php
/**
 * /sso/saml_settings.php
 * -------------------------------------------------
 * SAML Service Provider configuration
 *
 * PURPOSE
 * -------
 * This file defines the SAML configuration array consumed by
 * the SAML library (e.g. OneLogin PHP-SAML).
 *
 * It describes:
 *  - Service Provider (SP) identifiers and endpoints
 *  - Identity Provider (IdP) identifiers and endpoints
 *  - Security and signature requirements
 *  - Clock skew tolerance
 *
 * This file is **IdP-agnostic** and can be used with any
 * standards-compliant SAML 2.0 Identity Provider.
 */

// -------------------------------------------------
// Load base SAML configuration values
// -------------------------------------------------
require_once __DIR__ . '/config.php';

// -------------------------------------------------
// Return SAML configuration array
// -------------------------------------------------
return [

    /**
     * STRICT MODE
     * -----------
     * When enabled, the SAML library will strictly validate:
     *  - XML schema
     *  - Destination, audience, and issuer
     *  - Response and assertion signatures (if required)
     */
    'strict' => true,

    /**
     * DEBUG MODE
     * ----------
     * Enables verbose error output from the SAML library.
     * Should remain disabled in production environments.
     */
    'debug'  => false,

    // -------------------------------------------------
    // SERVICE PROVIDER (THIS APPLICATION)
    // -------------------------------------------------
    'sp' => [

        // Unique identifier for this Service Provider
        'entityId' => $SP_ENTITY_ID,

        // Assertion Consumer Service (ACS)
        // Receives SAML Responses from the IdP
        'assertionConsumerService' => [
            'url'     => $SP_ACS_URL,
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
        ],

        // Single Logout Service (SLO)
        // Used when initiating logout flows
        'singleLogoutService' => [
            'url'     => $SP_SLO_URL,
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ],

        /**
         * OPTIONAL SIGNING CONFIGURATION
         * ------------------------------
         * Provide a certificate and private key if you want
         * this application to sign AuthnRequests or LogoutRequests.
         */
        'x509cert'   => '',
        'privateKey' => '',
    ],

    // -------------------------------------------------
    // IDENTITY PROVIDER
    // -------------------------------------------------
    'idp' => [

        // Unique identifier for the Identity Provider
        'entityId' => $IDP_ENTITY_ID,

        // Single Sign-On (SSO) endpoint
        'singleSignOnService' => [
            'url'     => $IDP_SSO_URL,
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ],

        // Single Logout (SLO) endpoint
        'singleLogoutService' => [
            'url'     => $IDP_SLO_URL,
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ],

        // Public X.509 certificate used to verify IdP signatures
        'x509cert' => trim($IDP_X509_CERT),
    ],

    // -------------------------------------------------
    // SECURITY SETTINGS
    // -------------------------------------------------
    'security' => $SECURITY,

    /**
     * CLOCK SKEW TOLERANCE
     * -------------------
     * Allows a small time difference (in seconds) between
     * the SP and IdP clocks to avoid false expiry errors.
     */
    'allowed_clock_skew' => $ALLOWED_CLOCK_SKEW,
];

