# Bank-3 v1.1 — initial backend stabilization (partial)

Changes: atomic conditional debit for customer transfers; MySQL DECIMAL-based arithmetic using validated decimal strings; rollback on failed transfer; recipient validation inside transaction; CSRF token for customer transfer; generic database errors; session ID rotation on login; safer session cookie settings; educational cipher disclosure.

## Not yet completed
- CSRF protection across all other POST actions and logout.
- Authorization and input-validation review of every admin and employee endpoint.
- Encryption-key storage and decryption-log sanitization.
- Remove or rotate default demo credentials before any public deployment.
- Database constraints, migration strategy, concurrency and integration tests.
- UI/UX redesign and documentation.

## Local testing checklist
1. Import database.sql into a disposable MySQL instance; use PHP with PDO MySQL.
2. Set local DB credentials in database.php. Never deploy the bundled demo data publicly.
3. Verify admin/employee/customer logins and session rotation.
4. Test customer transfers: valid, insufficient balance, self-transfer, nonexistent recipient, invalid amounts, and invalid CSRF.
5. Test two concurrent transfers against the same balance. Confirm the balance never goes negative and debit/credit transaction rows match.
6. Review all other endpoints before hosting.

This is a **classroom banking simulation**, not production banking software. The patch has been syntax-checked, not end-to-end tested against a running MySQL server.
