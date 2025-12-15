ITFlow Agent SSO (SAML 2.0)
===========================

This implementation provides **Service Provider (SP)** support for **SAML 2.0 Single Sign-On (SSO)** for **ITFlow agent authentication**.

The solution is **vendor-neutral** and compatible with any standards-compliant **SAML 2.0 Identity Provider (IdP)**.

* * * * *

Scope
-----

-   SAML 2.0 authentication

-   HTTP-Redirect and HTTP-POST bindings

-   Local account mapping only (no auto-provisioning)

-   Supports SSO-only or hybrid (SSO + local login) modes

* * * * *

Tested Identity Providers
-------------------------

-   Duo SSO

> Other IdPs have not yet been tested but should work if they are SAML 2.0 compliant.

* * * * *

Known Non-Working Features
--------------------------

-   ITFlow session persistence and audit logging (partial / inconsistent)

-   Automatic user provisioning

* * * * *

Installation Overview
---------------------

### Login Entry Points

Two alternative login handlers are provided:

-   `/login-sso-only.php`\
    Always redirects users to the configured SSO provider.

-   `/login-sso-hybrid.php`\
    Adds an SSO login option to the standard ITFlow login page.

Replace the existing `/login.php` in the ITFlow web root with **one** of the above files, depending on your preferred authentication model.

### File Placement

-   Copy the entire `sso/` directory to the root of the ITFlow web directory.

-   Rename or remove the original `login.php`.

* * * * *

File Structure and Purpose
--------------------------

### `/sso/`

-   `README.md`\
    This documentation

-   `config.php`\
    Centralized SSO configuration

-   `login.php`\
    Initiates the SAML authentication redirect

-   `acs.php`\
    Assertion Consumer Service (handles SAML responses)

-   `slo.php`\
    Local logout handler

-   `entityid.php`\
    Service Provider metadata endpoint

-   `saml_settings.php`\
    Low-level SAML toolkit configuration

### Root-Level Login Variants

-   `/login.php`\
    Original ITFlow login file (rename or remove)

-   `/login-sso-only.php`\
    Enforced SSO login mode

-   `/login-sso-hybrid.php`\
    Hybrid SSO + local login mode

* * * * *

Authentication Model
--------------------

-   Authentication is performed by the **Identity Provider**.

-   ITFlow does **not** automatically create users.

-   A SAML login is accepted **only if**:

    -   The user exists locally in ITFlow

    -   The user is an active technician

    -   The account is not archived

    -   The user account is active in the IdP

* * * * *

Login Modes
-----------

### SSO-Only Mode

**File:** `login-sso-only.php`\
**Usage:** Replace `/login.php`

**Behavior:**

-   All login attempts are redirected directly to the IdP

-   Local username/password authentication is disabled

-   Recommended for enforced enterprise SSO environments

* * * * *

### Hybrid SSO + Local Mode

**File:** `login-sso-hybrid.php`\
**Usage:** Replace `/login.php`

**Behavior:**

-   Displays the standard ITFlow login form

-   Adds a **"Login with SSO"** button

-   Allows:

    -   Local authentication for break-glass or service accounts

    -   SSO authentication as the preferred method

* * * * *

Configuration
-------------

### `/sso/config.php`

This file controls all SSO-related behavior.

| Setting | Description |
| --- | --- |
| `$SAML_LOGIN_URL` | IdP Single Sign-On endpoint |
| `$SAML_ACS_URL` | Assertion Consumer Service URL |
| `$SAML_EMAIL_ATTRIBUTE` | Attribute used to extract the user email |
| `$REQUIRE_ATTRIBUTE_*` | Optional attribute enforcement rules |
| `$REPLAY_*` | Optional replay-attack protection |

Configuration is intentionally file-based and not stored in the database.

> A future enhancement may include a UI-based configuration stored directly in the database.

* * * * *

SAML Authentication Flow
------------------------

### 1\. User requests `/login.php`

-   Behavior depends on the selected login mode.

### 2\. `/sso/login.php`

-   Redirects the user to the IdP SSO endpoint.

-   Attempts to preserve optional parameters (`key`, `last_visited`), though this did not function reliably during testing.

### 3\. Identity Provider

-   Authenticates the user.

-   Sends a `SAMLResponse` via HTTP-POST to `/sso/acs.php`.

### 4\. `/sso/acs.php`

-   Decodes and validates the SAML response.

-   Extracts the user email (NameID with attribute fallback).

-   Maps the identity to a local technician account.

-   Creates an ITFlow session.

-   Records a login audit event.

-   Redirects the user to `/agent`.

* * * * *

Logout Flow
-----------

### `/sso/slo.php`

-   Destroys the local ITFlow session

-   Clears session cookies

-   Redirects the user to `/login.php`

This is **local logout only**.

IdP-initiated Single Logout (SLO) is not currently implemented but can be added later if required.

* * * * *

Audit Logging
-------------

Successful SSO logins are recorded using ITFlow's native audit logging:

-   **Type:** Login

-   **Action:** Success

-   **Message:** `user@example.com logged in via SAML SSO`

This ensures:

-   Consistent login history

-   Continued security monitoring

-   No database schema changes

* * * * *

Security Notes
--------------

-   HTTPS is strongly recommended

-   Email attributes are validated before use

-   Session cookies are HTTP-only

-   Optional replay-attack protection is supported

Assertion signature validation is **not currently enforced**.

If strict assertion validation is required, a full SAML toolkit should be integrated.

* * * * *

Known Limitations
-----------------

-   No automatic user provisioning

-   No role or group mapping

-   No IdP-initiated logout

-   No automatic SAML metadata rotation

* * * * *

Planned Enhancements (Optional)
-------------------------------

-   Administrative UI for SSO configuration

-   Attribute-to-role mapping

-   Enforced SSO policies

-   Signed assertion verification

-   Automatic user provisioning

-   SCIM support

* * * * *

License
-------

GNU General Public License v3
