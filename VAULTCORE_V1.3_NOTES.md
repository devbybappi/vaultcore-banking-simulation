# VaultCore v1.3 — Security update

## Changes
- Removed the unsafe, duplicate transfer handler in customer/dashboard.php. The dashboard transfer modal now posts to the existing atomic-debit, CSRF-protected customer/transfer.php endpoint.
- Added CSRF validation and hidden form tokens to login, registration, employee decrypt, admin employee and admin customer actions.
- New employee security keys are password-hashed rather than stored in plaintext. Legacy plaintext employee keys are upgraded to hashes after successful use.
- New decryption logs no longer store customer cipher keys; the admin logs view redacts displayed key values.
- Generic user-facing decryption errors avoid exposing exception details.

## Important limitations / follow-up
- This is a classroom banking/cryptography simulation, NOT a production financial system. Classical ciphers and existing customer cipher keys are not secure for real financial data.
- Previously recorded encryption_logs.key_used values may still contain sensitive keys. Review and purge historic values after backing up the database. For example, after reviewing the schema and making a backup: UPDATE encryption_logs SET key_used = '[REDACTED]';
- Login rate limits, session timeouts, secret migration, concurrency tests against real MySQL, and complete end-to-end testing remain outstanding.
- Back up the project and MySQL database before replacing files. No database schema changes are required for this patch.
