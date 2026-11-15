<?php
require_once '../session.php';
requireLogin(['admin']);

require_once '../database.php';
$db = new Database();
$conn = $db->getConnection();

$message = '';
$messageType = '';

// Handle employee actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) { http_response_code(403); exit('Invalid or expired form token. Refresh and try again.'); }
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $employeeId = 'EMP' . strtoupper(bin2hex(random_bytes(5)));
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $securityKey = $_POST['security_key'] ?? '';
        
        if (empty($fullName) || empty($email) || empty($password) || empty($securityKey)) {
            $message = 'Please fill in all fields';
            $messageType = 'error';
        } elseif (strlen($password) < 12) {
            $message = 'Password must be at least 12 characters';
            $messageType = 'error';
        } elseif (strlen($securityKey) < 12) {
            $message = 'Security key must be at least 12 characters';
            $messageType = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address';
            $messageType = 'error';
        } else {
            try {
                $stmt = $conn->prepare("SELECT id FROM employees WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $message = 'Email already exists';
                    $messageType = 'error';
                } else {
                    $passwordHash = hashPassword($password);
                    $stmt = $conn->prepare("INSERT INTO employees (employee_id, full_name, email, password_hash, security_key) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$employeeId, $fullName, $email, $passwordHash, password_hash($securityKey, PASSWORD_DEFAULT)]);
                    $message = 'Employee added successfully!';
                    $messageType = 'success';
                }
            } catch(PDOException $e) {
                error_log('VaultCore employee management database error');
                $message = 'Unable to complete request. Please try again.';
                $messageType = 'error';
            }
        }
    } elseif ($action === 'toggle_status') {
        $employeeId = $_POST['employee_id'] ?? '';
        $status = $_POST['status'] ?? 'active';
        $stmt = $conn->prepare("UPDATE employees SET status = ? WHERE id = ?");
        $stmt->execute([$status, $employeeId]);
        $message = 'Employee status updated';
        $messageType = 'success';
    } elseif ($action === 'delete') {
        $employeeId = $_POST['employee_id'] ?? '';
        $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->execute([$employeeId]);
        $message = 'Employee deleted';
        $messageType = 'success';
    }
}

$stmt = $conn->query("SELECT * FROM employees ORDER BY created_at DESC");
$employees = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Employees · VaultCore</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/admin.css">
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
            <a href="customers.php">
                <span class="nav-icon">👥</span>
                <span>Customers</span>
            </a>
            <a href="employees.php" class="active">
                <span class="nav-icon">👤</span>
                <span>Employees</span>
            </a>
            <a href="transactions.php">
                <span class="nav-icon">💳</span>
                <span>Transactions</span>
            </a>
            <a href="logs.php">
                <span class="nav-icon">📋</span>
                <span>Encryption Logs</span>
            </a>
            <a href="../auth/logout.php" class="logout">
                <span class="nav-icon">🚪</span>
                <span>Logout</span>
            </a>
        </nav>
    </div>

    <div class="main-content">
        <header class="page-header">
            <div>
                <h1>Manage Employees</h1>
                <p>Add, edit, or remove employees</p>
            </div>
            <button onclick="document.getElementById('addModal').classList.add('active')" class="btn-primary">
                ➕ Add Employee
            </button>
        </header>

        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
        <?php endif; ?>

        <div class="panel">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Security Key</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($employees)): ?>
                        <tr><td colspan="6" class="text-center">No employees found</td></tr>
                        <?php else: foreach ($employees as $e): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($e['employee_id']) ?></strong></td>
                            <td><?= htmlspecialchars($e['full_name']) ?></td>
                            <td><?= htmlspecialchars($e['email']) ?></td>
                            <td><code>•••••••• (hidden)</code></td>
                            <td>
                                <span class="badge badge-<?= $e['status'] ?>">
                                    <?= $e['status'] ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="employee_id" value="<?= $e['id'] ?>">
                                    <input type="hidden" name="status" value="<?= $e['status'] === 'active' ? 'inactive' : 'active' ?>">
                                    <button type="submit" class="btn-sm btn-toggle">
                                        <?= $e['status'] === 'active' ? '🔒' : '🔓' ?>
                                    </button>
                                </form>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="employee_id" value="<?= $e['id'] ?>">
                                    <button type="submit" class="btn-sm btn-delete">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Employee Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>➕ Add Employee</h2>
                <button class="modal-close" onclick="document.getElementById('addModal').classList.remove('active')">×</button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="Enter full name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Enter email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Create password (min 12 chars)" required>
                </div>
                <div class="form-group">
                    <label>Security Key</label>
                    <input type="text" name="security_key" placeholder="Enter unique security key" required>
                    <small>This key will be used by employee to decrypt customer data</small>
                </div>
                <button type="submit" class="btn-primary">Add Employee</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('addModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    </script>
    <script src="../js/admin.js"></script>
</body>
</html>