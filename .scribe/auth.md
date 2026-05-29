# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {TOKEN_JWT}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Obtenga el token en <code>POST /api/auth/login</code> y envie <code>Authorization: Bearer {TOKEN_JWT}</code>.
