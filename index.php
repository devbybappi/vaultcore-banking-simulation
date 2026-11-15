<?php
require_once 'session.php';

if (isLoggedIn()) {
    header('Location: ' . getUserType() . '/dashboard.php');
    exit;
}

$loginError = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>🏦 VaultCore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/vaultcore.css">
</head>
<body>
    <div class="app-container">
        <section class="hero-section">
            <div class="hero-content">
                <div class="logo-container">
                    <i class="fas fa-shield-alt"></i>
                    <h1>VaultCore</h1>
                </div>
                <p class="hero-tagline">Banking Management & Cryptography Simulation</p>
                <p class="hero-description">Explore banking workflows and classical cryptography in a learning environment</p>

                <?php if ($loginError): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($loginError); ?></div>
                <?php endif; ?>

                <div class="hero-buttons">
                    <a href="auth/login.php" class="btn btn-primary btn-large">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="auth/register.php" class="btn btn-outline btn-large">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                </div>
                <div class="cipher-badges">
                    <span class="badge">Vigenère</span>
                    <span class="badge">Rail Fence</span>
                    <span class="badge">Columnar</span>
                    <span class="badge">Hill</span>
                </div>
            </div>
            <div class="hero-image">
                <div class="feature-cards">
                    <div class="feature-card">
                        <i class="fas fa-user-shield"></i>
                        <h4>Admin</h4>
                        <p>Full control, manage employees & customers</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-user-tie"></i>
                        <h4>Employee</h4>
                        <p>Explore authorized customer records and cipher demonstrations</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-user"></i>
                        <h4>Customer</h4>
                        <p>Demo transfers & educational cipher-based notes</p>
                    </div>
                </div>
            </div>
        </section>
        <footer class="footer">
            <p>© 2026 VaultCore · An Educational Banking Simulation</p>
            <p class="footer-sub">Designed &amp; Developed by Abhijit Kar Bappi</p>
        </footer>
    </div>
    <script src="js/index.js"></script>
</body>
</html>
