<?php
define('APP_STARTED', true);
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/middleware/auth.php';
require_once __DIR__ . '/../app/helpers/logger.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);

// Fetch transaction
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$tx = $stmt->fetch();

if (!$tx) {
    header('Location: transactions.php');
    exit;
}

// Fetch categories
$cats = $pdo->prepare("SELECT id, name FROM categories WHERE user_id = ? AND is_deleted = 0");
$cats->execute([$user_id]);
$categories = $cats->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="container">
    <h1>Edit Transaction</h1>

    <form method="POST" action="../app/actions/transactions/edit_transaction_action.php">
        <input type="hidden" name="id" value="<?= $tx['id'] ?>">

        <p>Type: <?= ucfirst($tx['type']) ?></p>

        <select name="category_id">
            <option value="">-- Category --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $tx['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="description" placeholder="Description" value="<?= htmlspecialchars($tx['description']) ?>">

        <input type="number" step="0.01" name="amount" placeholder="Amount" value="<?= $tx['amount'] ?>" required>
        <input type="number" step="0.01" name="transaction_cost" placeholder="Transaction cost" value="<?= $tx['transaction_cost'] ?>">

        <button type="submit">Save Changes</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
