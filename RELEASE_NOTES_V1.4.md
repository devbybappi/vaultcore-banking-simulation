# VaultCore v1.4 — Release preparation

## Changes from supplied v1.3

1. Replaced leftover `Secure Bank` sidebar branding with `VaultCore` across PHP views.
2. Fixed admin customer suspend/activate/delete actions: JavaScript-generated forms now include the required CSRF token.
3. Validated customer IDs and status values on admin customer actions.
4. Removed employee security-key hashes and customer cipher keys from newly created login sessions. Employee dashboard now shows a non-secret reminder.
5. Admin cipher logs always redact `key_used` in the UI, including legacy database records.
6. Added a 30-minute authenticated session inactivity timeout.
7. Blocked browser execution of `test_encryption.php` (CLI only).
8. Corrected misleading admin demo-password comment in `database.sql`.
9. Added `README.md`, `.gitignore`, and this release checklist.

## Upgrade from v1.3

- Back up the entire working project and **export your MySQL database** first.
- Replace PHP/CSS/JS files with this package; **do not import `database.sql` again** when upgrading an existing database.
- No schema migration is required for these changes. Existing accounts and passwords remain in your database.
- Existing logged-in sessions should be logged out and signed in again.
- Confirm PHP/MySQL/XAMPP configuration and retest all roles.

## Manual regression checklist

- [ ] Admin/employee/customer login and logout; invalid passwords rejected
- [ ] Admin customer status toggling and deletion (on disposable test data)
- [ ] Admin employee creation and status actions
- [ ] Employee decryption with correct and incorrect security keys
- [ ] Customer transfer success, insufficient balance, self-transfer and missing recipient
- [ ] Transaction history and balances agree after transfer
- [ ] Invalid/missing CSRF tokens are rejected
- [ ] Idle authenticated session expires after 30 minutes
- [ ] Dashboard sidebar displays VaultCore consistently
- [ ] `test_encryption.php` returns 404 from a browser

## Known limitations / warnings

- **Local educational simulation only.** No security audit or database-backed concurrency test was performed in preparing this package.
- Demo SQL includes weak passwords and plaintext example cipher keys. Do not publish a running deployment with these defaults.
- Historical `encryption_logs.key_used` may contain keys. After a database backup and confirmation, run: `UPDATE encryption_logs SET key_used = '[REDACTED]';`
- Existing customer cipher keys are still stored in the database as required by this teaching exercise.
- No login rate limiting, deployment secret management, HTTPS enforcement, or full production security hardening.
- Account deletion cascades into related records per the existing schema. Use only disposable data when testing delete.
