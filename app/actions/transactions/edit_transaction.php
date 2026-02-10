<?php
define('APP_STARTED', true);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/balance_helper.php';
require_once __DIR__ . '/../../helpers/logger.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../../public/transactions.php');
    exit;
}

$id = (int)$_POST['id'];
$category_id = $_POST['category_id'] ?: null;
$description = trim($_POST['description']);
$amount = (float)$_POST['amount'];
$cost = (float)($_POST['transaction_cost'] ?? 0);

// Fetch original transaction
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$tx = $stmt->fetch();

if (!$tx || $amount <= 0) {
    header('Location: ../../../public/transactions.php');
    exit;
}

// Fetch all transactions for this user ordered by date
$stmt = $pdo->prepare("
    SELECT * FROM transactions 
    WHERE user_id = ? 
    ORDER BY created_at ASC, id ASC
");
$stmt->execute([$user_id]);
$all_transactions = $stmt->fetchAll();

$new_balance = 0.00;
$pdo->beginTransaction();

try {
    foreach ($all_transactions as $t) {

        if ($t['id'] == $id) {
            // Use edited values
            $t['amount'] = $amount;
            $t['transaction_cost'] = $cost;
            $t['category_id'] = $category_id;
            $t['description'] = $description;
        }

        // Compute new balance
        if ($t['type'] === 'expense') {
            $new_balance -= ($t['amount'] + $t['transaction_cost']);
            if ($new_balance < 0) {
                throw new Exception("Insufficient balance after edit.");
            }
        } else {
            $new_balance += $t['amount'];
        }

        // Update transaction balance
        $update = $pdo->prepare("
            UPDATE transactions 
            SET amount = ?, transaction_cost = ?, category_id = ?, description = ?, balance_after = ?
            WHERE id = ? AND user_id = ?
        ");
        $update->execute([
            $t['amount'],
            $t['transaction_cost'],
            $t['category_id'],
            $t['description'],
            $new_balance,
            $t['id'],
            $user_id
        ]);
    }

    // Update user balance
    updateBalance($pdo, $user_id, $new_balance);

    logActivity($pdo, $user_id, 'TRANSACTION_EDIT', "Edited transaction ID {$id}");

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
}

header('Location: ../../../public/transactions.php');
exit;
