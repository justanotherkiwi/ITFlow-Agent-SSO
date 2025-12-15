<?php
/**
 * /sso/acs.php
 * -------------------------------------------------
 * Assertion Consumer Service (ACS)
 *
 * PURPOSE
 * -------
 * This endpoint receives and processes the SAMLResponse
 * posted by the Identity Provider (IdP) after a successful
 * authentication.
 *
 * Responsibilities:
 *   - Accept and decode the SAMLResponse
 *   - Extract the authenticated user identity
 *   - Map the identity to a local technician account
 *   - Establish an authenticated ITFlow session
 *   - Record a successful login event
 *   - Redirect the user to the agent interface
 *
 * NOTE
 * ----
 * This implementation intentionally keeps validation
 * lightweight and assumes trust is established at the
 * IdP level.
 */

// -------------------------------------------------
// TEMPORARY: allow HTTP if HTTPS enforcement is disabled
// -------------------------------------------------
$config_https_only = false;

// -------------------------------------------------
// Bootstrap application
// -------------------------------------------------
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/config.php';

// -------------------------------------------------
// Secure session handling
// -------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', true);
    session_start();
}

// -------------------------------------------------
// Require SAML response payload
// -------------------------------------------------
/**
 * The IdP must POST a base64-encoded SAMLResponse.
 */
if (empty($_POST['SAMLResponse'])) {
    http_response_code(400);
    exit('Missing SAMLResponse');
}

$xml = base64_decode($_POST['SAMLResponse']);
if ($xml === false) {
    http_response_code(400);
    exit('Invalid SAMLResponse encoding');
}

// -------------------------------------------------
// Parse and validate XML structure
// -------------------------------------------------
libxml_use_internal_errors(true);

$dom = new DOMDocument();
if (!$dom->loadXML($xml)) {
    http_response_code(400);
    exit('Invalid SAML XML');
}

$xpath = new DOMXPath($dom);
$xpath->registerNamespace(
    'saml',
    'urn:oasis:names:tc:SAML:2.0:assertion'
);

// -------------------------------------------------
// Extract authenticated user identity
// -------------------------------------------------
/**
 * Identity resolution order:
 *   1. NameID
 *   2. Configured attribute (fallback)
 */
$email = null;

// Attempt NameID first
$nameId = $xpath->query('//saml:NameID')->item(0);
if ($nameId) {
    $email = trim($nameId->textContent);
}

// Fallback to attribute-based lookup
if (!$email) {
    foreach ($xpath->query('//saml:Attribute') as $attr) {
        if (
            strcasecmp(
                $attr->getAttribute('Name'),
                $SAML_EMAIL_ATTRIBUTE
            ) === 0
        ) {
            $vals = $attr->getElementsByTagName('AttributeValue');
            if ($vals->length > 0) {
                $email = trim($vals->item(0)->textContent);
                break;
            }
        }
    }
}

// Normalize and validate identity
$email = strtolower(trim((string)$email));

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(401);
    exit('Invalid email from SAML assertion');
}

// -------------------------------------------------
// Lookup local technician account
// -------------------------------------------------
$email_safe = sanitizeInput($email);

$user = mysqli_fetch_assoc(mysqli_query(
    $mysqli,
    "
    SELECT *
    FROM users
    WHERE user_email = '$email_safe'
      AND user_type = 1
      AND user_status = 1
      AND user_archived_at IS NULL
    LIMIT 1
    "
));

if (!$user) {
    http_response_code(403);
    exit('No matching local technician account');
}

$user_id = (int) $user['user_id'];

// -------------------------------------------------
// Establish authenticated session
// -------------------------------------------------
$_SESSION['user_id']    = $user_id;
$_SESSION['csrf_token'] = bin2hex(random_bytes(78));
$_SESSION['logged']     = true;

// -------------------------------------------------
// Audit logging
// -------------------------------------------------
/**
 * ITFlow uses audit log entries to track
 * successful and failed authentication events.
 */
logAction(
    'Login',
    'Success',
    "$email logged in via SAML SSO",
    0,
    $user_id
);

// -------------------------------------------------
// Redirect to agent dashboard
// -------------------------------------------------
redirect('/agent');

