<?php
require_once '../session.php';
requireLogin(['customer']);

require_once '../database.php';
$db = new Database();
$conn = $db->getConnection();

$customerId = $_SESSION['user_id'];
$accountNumber = $_SESSION['account_number'] ?? '';

$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customerId]);
$customer = $stmt->fetch();

$stmt = $conn->prepare("SELECT id, full_name, account_number FROM customers WHERE id != ? AND status = 'active'");
$stmt->execute([$customerId]);
$recipients = $stmt->fetchAll();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $toAccount = trim((string)($_POST['to_account'] ?? ''));
    $amountInput = trim((string)($_POST['amount'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Session expired. Refresh the page and try again.';
        $messageType = 'error';
    } elseif (!preg_match('/^(?:0|[1-9][0-9]{0,12})\.[0-9]{2}$/', $amountInput) || (float)$amountInput <= 0) {
        $message = 'Enter a positive amount with exactly two decimal places (e.g. 10.00).';
        $messageType = 'error';
    } elseif (strlen($description) > 250) {
        $message = 'Description must be 250 characters or fewer.';
        $messageType = 'error';
    } elseif ($toAccount === $accountNumber) {
        $message = 'Cannot transfer to yourself.';
        $messageType = 'error';
    } else {
        try {
            $conn->beginTransaction();
            // A conditional, atomic debit prevents two concurrent requests from
            // spending the same balance. MySQL DECIMAL handles the exact cents.
            $debit = $conn->prepare("UPDATE customers SET balance = balance - :amount WHERE id = :id AND status = 'active' AND balance >= :minimum");
            $debit->execute(['amount' => $amountInput, 'id' => $customerId, 'minimum' => $amountInput]);
            if ($debit->rowCount() !== 1) {
                throw new RuntimeException('Insufficient balance or inactive account.');
            }
            $recipientStmt = $conn->prepare("SELECT id FROM customers WHERE account_number = ? AND status = 'active'");
            $recipientStmt->execute([$toAccount]);
            $recipient = $recipientStmt->fetch();
            if (!$recipient || (int)$recipient['id'] === (int)$customerId) {
                throw new RuntimeException('Recipient account unavailable.');
            }
            $credit = $conn->prepare("UPDATE customers SET balance = balance + ? WHERE id = ? AND status = 'active'");
            $credit->execute([$amountInput, $recipient['id']]);
            if ($credit->rowCount() !== 1) {
                throw new RuntimeException('Recipient account unavailable.');
            }
            $tx = $conn->prepare("INSERT INTO transactions (transaction_id, customer_id, from_account, to_account, amount, transaction_type, description, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'completed')");
            $tx->execute([generateTransactionId(), $customerId, $accountNumber, $toAccount, '-' . $amountInput, 'transfer', $description]);
            $tx->execute([generateTransactionId(), $recipient['id'], $accountNumber, $toAccount, $amountInput, 'credit', $description]);
            $conn->commit();
            $message = 'Transfer of $' . number_format((float)$amountInput, 2) . ' sent successfully!';
            $messageType = 'success';
            $refresh = $conn->prepare('SELECT * FROM customers WHERE id = ?');
            $refresh->execute([$customerId]);
            $customer = $refresh->fetch();
        } catch (Throwable $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            error_log('Bank transfer failure: ' . $e->getMessage());
            $message = $e instanceof RuntimeException ? $e->getMessage() : 'Transfer could not be completed. Please try again.';
            $messageType = 'error';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Money · VaultCore</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/customer.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/vaultcore.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <span class="brand-icon">🏦</span>
            <span>VaultCore</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php">
                <span class="nav-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="transfer.php" class="active">
                <span class="nav-icon">💸</span>
                <span>Transfer</span>
            </a>
            <a href="transactions.php">
                <span class="nav-icon">📋</span>
                <span>Transactions</span>
            </a>
            <a href="profile.php">
                <span class="nav-icon">👤</span>
                <span>Profile</span>
            </a>
            <a href="../auth/logout.php" class="logout">
                <span class="nav-icon">🚪</span>
                <span>Logout</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <span class="user-avatar"><?= strtoupper(substr($_SESSION['full_name'] ?? 'C', 0, 1)) ?></span>
                <div>
                    <strong><?= htmlspecialchars($_SESSION['full_name'] ?? 'Customer') ?></strong>
                    <small>Customer</small>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        <header class="page-header">
            <div>
                <h1>💸 Transfer Money</h1>
                <p>Send money securely to other accounts</p>
            </div>
            <div class="balance-display">
                <span>Available Balance: </span>
                <strong>$<?= number_format($customer['balance'] ?? 0, 2) ?></strong>
            </div>
        </header>

        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="panel transfer-panel">
            <div class="transfer-container">
                <div class="transfer-form-wrapper">
                    <h2>Send Money</h2>
                    <form method="POST" class="transfer-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                        <div class="form-group">
                            <label>Recipient Account Number</label>
                            <input type="text" name="to_account" placeholder="Enter recipient account number" required>
                            <small>Enter the 6-digit account number of the recipient</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Amount ($)</label>
                            <input type="number" name="amount" placeholder="0.00" step="0.01" min="0.01" max="9999999999999.99" required>
                            <small>Maximum: $<?= number_format($customer['balance'] ?? 0, 2) ?></small>
                        </div>
                        
                        <div class="form-group">
                            <label>Description (Optional)</label>
                            <input type="text" name="description" placeholder="What's this for?">
                        </div>
                        
                        <button type="submit" class="btn-primary">Send Money</button>
                    </form>
                </div>
                
                <div class="recipients-list">
                    <h3>Quick Recipients</h3>
                    <?php if (empty($recipients)): ?>
                        <p class="text-muted">No other customers found</p>
                    <?php else: foreach ($recipients as $r): ?>
                        <div class="recipient-card" onclick="setRecipient('<?= $r['account_number'] ?>')">
                            <span class="recipient-avatar"><?= strtoupper(substr($r['full_name'], 0, 1)) ?></span>
                            <div>
                                <div class="recipient-name"><?= htmlspecialchars($r['full_name']) ?></div>
                                <div class="recipient-account"><?= htmlspecialchars($r['account_number']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>

        <div class="encryption-banner">
            <span>🔐</span>
            <span>This is a banking simulation. Classical ciphers are for educational demonstration only; they do not secure transfers.</span>
        </div>
    </div>

    <script>
        function setRecipient(account) {
            document.querySelector('input[name="to_account"]').value = account;
        }
    </script>
</body>
</html>