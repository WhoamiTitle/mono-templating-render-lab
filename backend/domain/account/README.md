# Account Domain (LK)

Domain module for personal cabinet authorization using `email + password`.

## Iteration Scope

- MVP (current): registration, login, logout, password change in profile.
- Deferred (post-MVP): forgot/reset password flow via `PasswordResetToken`.

## MVP Auth Flow (Session-Based, No JWT)

1. Check `email + password hash`.
2. Create server-side session (`AuthSession`) with `expiresAt`.
3. Issue `session_id` in `HttpOnly` cookie.
4. Validate session on every request (`assertActive`).
5. Logout via `revoke`.

## Aggregate Roots

- `User`
- `AuthSession`
- `PasswordResetToken`

## Main Rules

- User email is normalized and validated.
- Password hash is required for every user.
- Blocked user cannot authenticate.
- Session is active only when not revoked and not expired.
- Password reset token is one-time and expires by time.
- Template and render access is restricted by owner id in template/render domain modules.
