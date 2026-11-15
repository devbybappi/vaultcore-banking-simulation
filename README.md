# VaultCore

**Educational Banking Management & Classical Cryptography Simulation**

VaultCore is a PHP/MySQL classroom project demonstrating role-based dashboards, simulated bank accounts and transfers, and historical/classical cipher exercises. **Not a real bank or a production-ready security system.** No real funds or confidential information should be used.

## Main features

- Admin: customer/employee management, transaction overview, cipher activity logs
- Employee: customer lookup and classical cipher decryption demonstration
- Customer: registration, account overview, simulated transfers, transaction history, profile
- Role-based authentication, CSRF-protected state-changing forms, password hashing, and session handling
- Educational Vigenère, Rail Fence, Columnar Transposition, and Hill cipher modules

## Tech stack

PHP, MySQL (PDO), HTML, CSS, JavaScript. Tested syntax with PHP CLI; full database-backed testing requires a local PHP/MySQL server.

## Local installation (XAMPP)

1. Back up any existing `bank-3` directory and export its MySQL database.
2. Extract the `bank-3` folder into `C:\xampp\htdocs\` (Windows) or your server's web root.
3. Start Apache and MySQL. In phpMyAdmin, import `database.sql` into a **new/disposable database**. **Do not re-import it over a populated database**; it contains CREATE and demo INSERT statements.
4. Set the local connection values in `database.php` (`secure_bank`, `localhost`, local MySQL user/password). Do not commit actual credentials.
5. Open `http://localhost/bank-3/` and test the three roles.

### Seed accounts (local demo only)

| Role | Identifier | Password |
| --- | --- | --- |
| Admin | `admin` | `password` |
| Employee | `sarah@securebank.com` | `password` |
| Customer | `john@email.com` | `password` |

Additional seeded users are in `database.sql`. These intentionally weak credentials are for an isolated classroom environment only. Replace them before any network-accessible deployment. Employee demonstration security keys are seeded in the SQL file.

## Safety and scope

- Classical ciphers are **not** modern encryption. Customer cipher keys are stored in the demonstration database and must not protect real secrets.
- This release is not independently audited, penetration-tested, or validated for real financial transactions.
- Simulated transfer debits use a conditional atomic SQL update inside a transaction. Test concurrency on your own MySQL setup.
- Existing databases may contain historic plaintext `encryption_logs.key_used` values; after backup and review, redact them (see `RELEASE_NOTES_V1.4.md`).
- Login rate limiting and broader production security controls are **not implemented**. Use only on localhost or a private test environment.

## Structure

`admin/`, `employee/`, `customer/` contain role-specific pages; `auth/` contains login and registration; `css/` and `js/` hold UI assets; `session.php` provides session/CSRF helpers; `database.php` provides the PDO connection; cipher PHP files implement the educational algorithms.

## Documentation

Read `RELEASE_NOTES_V1.4.md` for the change list, upgrade checklist, known limitations, and manual regression tests.

**Developer:** Abhijit Kar Bappi · [GitHub](https://github.com/devbybappi)
"# vaultcore-banking-simulation" 
