<?php
define('APP_STARTED', true);
session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/balance_helper.php';
require_once __DIR__ . '/../../helpers/logger.php';

$user_id = $_SESSION['user_id'];

// Only POST requests allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../../public/transactions.php');
    exit;
}

// Get & sanitize inputs
$type        = $_POST['type'] ?? '';
$category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
$description = trim($_POST['description'] ?? '');
$amount      = (float) ($_POST['amount'] ?? 0);
$cost        = (float) ($_POST['transaction_cost'] ?? 0);

// Basic validation
if ($amount <= 0 || !in_array($type, ['expense', 'refill'])) {
    header('Location: ../../../public/transactions.php?error=invalid_input');
    exit;
}

// -------------------------
// Ensure balance row exists
// -------------------------
$stmt = $pdo->prepare("SELECT current_balance FROM balances WHERE user_id = ?");
$stmt->execute([$user_id]);
$balanceRow = $stmt->fetch();

if (!$balanceRow) {
    // Insert a new balance row for the user
    $stmtInsert = $pdo->prepare("INSERT INTO balances (user_id, current_balance) VALUES (?, 0.00)");
    $stmtInsert->execute([$user_id]);
    $current_balance = 0.00;
} else {
    $current_balance = (float)$balanceRow['current_balance'];
}

// Calculate new balance
if ($type === 'expense') {
    $total = $amount + $cost;
    $new_balance = $current_balance - $total;

    if ($new_balance < 0) {
        $msg = "Insufficient balance. You have KES {$current_balance}, but tried to spend KES {$total}.";
        header("Location: ../../../public/transactions.php?error=" . urlencode($msg));
        exit;
    }
} else {
    $new_balance = $current_balance + $amount;
}

// Start transaction
$pdo->beginTransaction();

try {
    // Insert transaction
    $stmt = $pdo->prepare("
        INSERT INTO transactions 
        (user_id, category_id, type, description, amount, transaction_cost, balance_after)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $user_id,
        $category_id,
        $type,
        $description,
        $amount,
        $cost,
        $new_balance
    ]);

    // Update balance
    updateBalance($pdo, $user_id, $new_balance);

    // Log activity
    logActivity(
        $pdo,
        $user_id,
        'TRANSACTION_ADD',
        ucfirst($type) . " of KES {$amount}"
    );

    $pdo->commit();

    header('Location: ../../../public/transactions.php?success=1');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $msg = urlencode($e->getMessage());
    header("Location: ../../../public/transactions.php?error={$msg}");
    exit;
}
