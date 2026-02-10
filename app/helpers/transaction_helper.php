<?php
// ================================================
// Balance & Transaction Helper
// Provides functions to get/update user balance
// ================================================

if (!defined('APP_STARTED')) {
    die('Direct access not allowed');
}

/**
 * Get the current balance of a user
 * @param PDO $pdo - PDO connection
 * @param int $user_id - ID of the user
 * @return float - current balance
 */
function getCurrentBalance(PDO $pdo, int $user_id): float {
    $stmt = $pdo->prepare("SELECT balance FROM balances WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
    return $row ? (float)$row['balance'] : 0.00;
}

/**
 * Update the user's balance
 * @param PDO $pdo - PDO connection
 * @param int $user_id - ID of the user
 * @param float $new_balance - new balance amount
 * @return bool - true on success
 */
function updateBalance(PDO $pdo, int $user_id, float $new_balance): bool {
    $stmt = $pdo->prepare("UPDATE balances SET balance = ?, updated_at = NOW() WHERE user_id = ?");
    return $stmt->execute([$new_balance, $user_id]);
}

/**
 * Record a transaction
 * @param PDO $pdo
 * @param int $user_id
 * @param string $type - 'expense' or 'refill'
 * @param string $description
 * @param float $amount
 * @param float $transaction_cost
 * @param float|null $balance_after - if null, auto-calculate
 * @param int|null $category_id
 * @return bool
 */
function addTransaction(PDO $pdo, int $user_id, string $type, string $description, float $amount, float $transaction_cost = 0.00, float $balance_after = null, ?int $category_id = null): bool {

    // If balance_after not provided, calculate
    if ($balance_after === null) {
        $current_balance = getCurrentBalance($pdo, $user_id);
        if ($type === 'expense') {
            $balance_after = $current_balance - $amount;
        } else if ($type === 'refill') {
            $balance_after = $current_balance + $amount;
        } else {
            throw new Exception("Invalid transaction type: $type");
        }
        // Update the balance
        updateBalance($pdo, $user_id, $balance_after);
    }

    $stmt = $pdo->prepare("
        INSERT INTO transactions 
        (user_id, category_id, type, description, amount, transaction_cost, balance_after, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    return $stmt->execute([$user_id, $category_id, $type, $description, $amount, $transaction_cost, $balance_after]);
}
