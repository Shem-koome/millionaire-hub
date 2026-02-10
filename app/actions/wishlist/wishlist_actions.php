<?php
define('APP_STARTED', true);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/balance_helper.php';
require_once __DIR__ . '/../../helpers/logger.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../../public/wishlist.php');
    exit;
}

$action = $_POST['action'] ?? '';
$id     = (int) ($_POST['id'] ?? 0);

// ================= ADD ITEM =================
if ($action === 'add') {
    $item_name  = trim($_POST['item_name']);
    $category_id = $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
    $estimated_amount = isset($_POST['estimated_amount']) ? (float)$_POST['estimated_amount'] : 0;
    $notes = trim($_POST['notes']) ?: null;

    if ($item_name === '') {
        header('Location: ../../../public/wishlist.php?error=NameRequired');
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO wishlist_items 
            (user_id, category_id, item_name, estimated_amount, notes, status)
            VALUES (?, ?, ?, ?, ?, 'not_bought')
        ");
        $stmt->execute([$user_id, $category_id, $item_name, $estimated_amount, $notes]);

        logActivity($pdo, $user_id, 'WISHLIST_ADD', "Added {$item_name}");
        header('Location: ../../../public/wishlist.php?success=1');
        exit;
    } catch (PDOException $e) {
        header('Location: ../../../public/wishlist.php?error=' . urlencode($e->getMessage()));
        exit;
    }
}

// ================= MARK AS BOUGHT =================
if ($action === 'bought') {
    $stmt = $pdo->prepare("SELECT * FROM wishlist_items WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    $item = $stmt->fetch();

    if (!$item) {
        header('Location: ../../../public/wishlist.php?error=ItemNotFound');
        exit;
    }

    $amount = $item['estimated_amount'];
    $current_balance = getCurrentBalance($pdo, $user_id);

    if ($current_balance < $amount) {
        header('Location: ../../../public/wishlist.php?error=InsufficientBalance');
        exit;
    }

    $new_balance = $current_balance - $amount;

    $pdo->beginTransaction();
    try {
        // Update wishlist status
        $stmt = $pdo->prepare("UPDATE wishlist_items SET status = 'bought', actual_amount = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$amount, $id, $user_id]);

        // Insert expense transaction
        $stmt = $pdo->prepare("
            INSERT INTO transactions 
            (user_id, category_id, type, description, amount, transaction_cost, balance_after)
            VALUES (?, ?, 'expense', ?, ?, 0, ?)
        ");
        $stmt->execute([$user_id, $item['category_id'], $item['item_name'], $amount, $new_balance]);

        // Update balance
        updateBalance($pdo, $user_id, $new_balance);

        logActivity($pdo, $user_id, 'WISHLIST_BOUGHT', "Bought {$item['item_name']} for KES {$amount}");

        $pdo->commit();
        header('Location: ../../../public/wishlist.php?success=1');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        header('Location: ../../../public/wishlist.php?error=' . urlencode($e->getMessage()));
        exit;
    }
}

// ================= DROP ITEM =================
if ($action === 'drop') {
    $drop_reason = trim($_POST['drop_reason'] ?? '');
    if ($drop_reason === '') {
        header('Location: ../../../public/wishlist.php?error=ReasonRequired');
        exit;
    }

    $stmt = $pdo->prepare("UPDATE wishlist_items SET status = 'dropped', drop_reason = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$drop_reason, $id, $user_id]);

    logActivity($pdo, $user_id, 'WISHLIST_DROPPED', "Dropped wishlist item ID {$id} for reason: {$drop_reason}");
    header('Location: ../../../public/wishlist.php?success=1');
    exit;
}
