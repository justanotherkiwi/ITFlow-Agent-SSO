<?php
/**
 * /sso/config.php
 * -------------------------------------------------
 * Central SAML / SSO configuration file
 *
 * PURPOSE
 * -------
 * This file defines all Service Provider (SP) level
 * configuration used by the SAML / SSO implementation.
 *
 * Values defined here are consumed by:
 *   - login.php   (SSO initiation)
 *   - acs.php     (assertion handling)
 *   - entityid.php (SP metadata)
 *
 * This file is intentionally vendor-neutral and
 * supports any SAML 2.0 compliant Identity Provider.
 */

// -------------------------------------------------
// Identity Provider (IdP) login endpoint
// -------------------------------------------------
/**
 * The URL where users are redirected to authenticate.
 * This is the IdP's Single Sign-On (SSO) service URL.
 */
$SAML_LOGIN_URL = 'https://idp.example.com/saml/sso';

// -------------------------------------------------
// Assertion Consumer Service (ACS) endpoint
// -------------------------------------------------
/**
 * The URL where the IdP POSTs the SAMLResponse after
 * successful authentication.
 */
$SAML_ACS_URL = 'https://your-app.example.com/sso/acs.php';

// -------------------------------------------------
// User identity extraction
// -------------------------------------------------
/**
 * Attribute name used to extract the user's email
 * address from the SAML assertion.
 *
 * If not found, NameID will be used as a fallback.
 */
$SAML_EMAIL_ATTRIBUTE = 'email';

// -------------------------------------------------
// Optional: attribute-based access control
// -------------------------------------------------
/**
 * Optionally require a specific attribute/value
 * pair to be present in the SAML assertion.
 *
 * Set $REQUIRE_ATTRIBUTE_NAME to null to disable
 * this check entirely.
 *
 * Example use cases:
 *   - group membership
 *   - entitlement flags
 *   - role assertions
 */
$REQUIRE_ATTRIBUTE_NAME  = null;   // e.g. 'role'
$REQUIRE_ATTRIBUTE_VALUE = 'true'; // e.g. 'admin'

// -------------------------------------------------
// Optional: replay protection
// -------------------------------------------------
/**
 * Simple replay protection to prevent reuse of
 * SAML assertions.
 *
 * Assertion IDs are cached locally for a short
 * period of time and rejected if reused.
 *
 * This is not a replacement for full signature
 * validation but provides cheap additional safety.
 */
$REPLAY_CACHE_FILE  = __DIR__ . '/_replay_cache.json';
$REPLAY_TTL_SECONDS = 300; // seconds (default: 5 minutes)

