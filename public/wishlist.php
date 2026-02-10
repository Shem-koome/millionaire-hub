<?php
define('APP_STARTED', true);

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/middleware/auth.php';
require_once __DIR__ . '/../app/helpers/balance_helper.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Fetch categories for dropdown
$stmt = $pdo->prepare("SELECT id, name FROM categories WHERE user_id = ? AND is_deleted = 0");
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll();

// Fetch wishlist items
$stmt = $pdo->prepare("
    SELECT w.*, c.name AS category_name
    FROM wishlist_items w
    LEFT JOIN categories c ON c.id = w.category_id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$stmt->execute([$user_id]);
$wishlist = $stmt->fetchAll();

// Get current balance
$current_balance = getCurrentBalance($pdo, $user_id);

?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="container">
    <h1>Wishlist</h1>

    <!-- Success/Error Messages -->
    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success">Action completed successfully!</div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <!-- ADD ITEM -->
    <form class="card" method="POST" action="../app/actions/wishlist/wishlist_actions.php">
        <h3>Add Item</h3>
        <input type="hidden" name="action" value="add">
        <input type="text" name="item_name" placeholder="Item name" required>
        <select name="category_id">
            <option value="">-- Category --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" step="0.01" name="estimated_amount" placeholder="Estimated amount">
        <textarea name="notes" placeholder="Notes"></textarea>
        <button type="submit">Add to Wishlist</button>
    </form>

    <!-- LIST ITEMS -->
    <div class="card">
        <h3>Your Wishlist</h3>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Estimated Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($wishlist as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                        <td><?= htmlspecialchars($item['category_name'] ?? '-') ?></td>
                        <td>KES <?= number_format($item['estimated_amount'], 2) ?></td>
                        <td><?= ucfirst(str_replace('_', ' ', $item['status'])) ?></td>
                        <td>
                            <?php if ($item['status'] === 'not_bought'): ?>
                                <form style="display:inline;" method="POST" action="../app/actions/wishlist/wishlist_actions.php">
                                    <input type="hidden" name="action" value="bought">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <button type="submit">Mark as Bought</button>
                                </form>
                                <form style="display:inline;" method="POST" action="../app/actions/wishlist/wishlist_actions.php">
                                    <input type="hidden" name="action" value="drop">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <input type="text" name="drop_reason" placeholder="Reason" required>
                                    <button type="submit">Drop</button>
                                </form>
                            <?php else: ?>
                                <span>-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
