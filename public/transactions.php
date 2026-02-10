<?php 
define('APP_STARTED', true);

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/middleware/auth.php';
require_once __DIR__ . '/../app/helpers/balance_helper.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

// Get current balance
$current_balance = getCurrentBalance($pdo, $user_id);

// Fetch categories
$cats = $pdo->prepare("SELECT id, name FROM categories WHERE user_id = ? AND is_deleted = 0");
$cats->execute([$user_id]);
$categories = $cats->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent transactions
$stmt = $pdo->prepare("
    SELECT t.*, c.name AS category_name
    FROM transactions t
    LEFT JOIN categories c ON c.id = t.category_id
    WHERE t.user_id = ?
    ORDER BY t.created_at DESC
    LIMIT 50
");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="dashboard">
    <h1>Dashboard</h1>

    <?php if ($current_balance < 500): ?>
        <div class="alert alert-error">
            ⚠️ Low balance! You have KES <?= number_format($current_balance, 2) ?>
        </div>
    <?php endif; ?>

    <div class="cards">
        <div class="card balance">
            <h3>Current Balance</h3>
            <p>KES <?= number_format($current_balance, 2); ?></p>
        </div>
        <div class="card">
            <h3>Recent Transactions</h3>
            <p><?= count($transactions) ?> transactions</p>
        </div>
    </div>

    <!-- ADD TRANSACTION FORM -->
    <div class="card">
        <h3>Add Transaction</h3>
        <form method="POST" action="../app/actions/transactions/add_transaction.php">
            <select name="type" required>
                <option value="expense">Expense</option>
                <option value="refill">Refill</option>
            </select>

            <select name="category_id">
                <option value="">-- Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <input type="text" name="description" placeholder="Description">
            <input type="number" step="0.01" name="amount" placeholder="Amount" required>
            <input type="number" step="0.01" name="transaction_cost" placeholder="Transaction cost (optional)">

            <button type="submit">Save</button>
        </form>
    </div>

<!-- TRANSACTION HISTORY -->
<div class="card">
    <h3>Transaction History</h3>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Cost</th>
                    <th>Balance After</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $tx): ?>
                    <tr>
                        <td><?= date('d M Y H:i', strtotime($tx['created_at'])) ?></td>
                        <td><?= ucfirst($tx['type']) ?></td>
                        <td><?= htmlspecialchars($tx['category_name'] ?? '-') ?></td>
                        <td>KES <?= number_format($tx['amount'], 2) ?></td>
                        <td>KES <?= number_format($tx['transaction_cost'], 2) ?></td>
                        <td>KES <?= number_format($tx['balance_after'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<?php include '../includes/footer.php'; ?>
