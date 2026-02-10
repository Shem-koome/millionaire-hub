<?php
if (!defined('APP_STARTED')) {
    die('Direct access not allowed');
}

/**
 * Get the current balance of a user
 */
function getCurrentBalance(PDO $pdo, int $user_id): float {
    $stmt = $pdo->prepare("SELECT current_balance FROM balances WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (float)$row['current_balance'] : 0.00;
}

/**
 * Update the user's balance
 */
function updateBalance(PDO $pdo, int $user_id, float $new_balance): bool {
    // If balance row doesn't exist, insert it
    $stmt = $pdo->prepare("SELECT id FROM balances WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $exists = $stmt->fetch();

    if ($exists) {
        $stmt = $pdo->prepare("UPDATE balances SET current_balance = ?, updated_at = NOW() WHERE user_id = ?");
        return $stmt->execute([$new_balance, $user_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO balances (user_id, current_balance, updated_at) VALUES (?, ?, NOW())");
        return $stmt->execute([$user_id, $new_balance]);
    }
}

/**
 * Add a transaction and update balance automatically
 */
function addTransaction(PDO $pdo, int $user_id, string $type, string $description, float $amount, float $transaction_cost = 0.00, ?int $category_id = null): bool {
    $current_balance = getCurrentBalance($pdo, $user_id);

    if ($type === 'expense') {
        $balance_after = $current_balance - ($amount + $transaction_cost);
        if ($balance_after < 0) {
            throw new Exception("Insufficient balance. Current balance: KES {$current_balance}, trying to spend KES " . ($amount + $transaction_cost));
        }
    } elseif ($type === 'refill') {
        $balance_after = $current_balance + $amount;
    } else {
        throw new Exception("Invalid transaction type: $type");
    }

    $stmt = $pdo->prepare("
        INSERT INTO transactions
        (user_id, category_id, type, description, amount, transaction_cost, balance_after, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $success = $stmt->execute([$user_id, $category_id, $type, $description, $amount, $transaction_cost, $balance_after]);

    if ($success) {
        updateBalance($pdo, $user_id, $balance_after);
    }

    return $success;
}
