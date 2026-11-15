# VaultCore v1.2 — Branding and UI refresh

Based on Bank-3 v1.1 partial security update.

- Renamed FortDice to VaultCore across page branding, browser titles and landing JavaScript.
- Updated landing tagline to accurately describe an educational banking/cryptography simulation. Classical ciphers are NOT modern secure encryption.
- Updated homepage footer and developer credit.
- Added `css/vaultcore.css` as a shared design layer on all PHP pages, providing navy sidebars, lighter content areas, blue buttons and green success accents.
- Kept original PHP business logic, SQL schema, routes, and security fixes unchanged.

## Install
1. Back up the existing `htdocs/bank-3` directory and database.
2. Extract this archive into `htdocs`, replacing the `bank-3` folder.
3. Open `http://localhost/bank-3/index.php`. Hard refresh (Ctrl+F5).
4. Check login, register, admin, employee, customer, transfer, and mobile pages.

## Testing
PHP syntax check performed if CLI PHP is available; no live MySQL integration or browser visual regression testing was performed. This is an educational banking simulation, not production banking software.
