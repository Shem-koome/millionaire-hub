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

// =====================
// CURRENT BALANCE
// =====================
$current_balance = getCurrentBalance($pdo, $user_id);

// =====================
// FUNCTION TO GET TOTALS
// =====================
function getTotals($pdo, $user_id, $start_date = null, $end_date = null) {
    $query = "
        SELECT 
            SUM(CASE WHEN type='expense' THEN amount + transaction_cost ELSE 0 END) AS total_expense,
            SUM(CASE WHEN type='refill' THEN amount ELSE 0 END) AS total_refill
        FROM transactions
        WHERE user_id = ?
    ";

    $params = [$user_id];

    if ($start_date && $end_date) {
        $query .= " AND DATE(created_at) BETWEEN ? AND ?";
        $params[] = $start_date;
        $params[] = $end_date;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// =====================
// TODAY
// =====================
$today = date('Y-m-d');
$todayTotals = getTotals($pdo, $user_id, $today, $today);
$today_spending = (float) ($todayTotals['total_expense'] ?? 0);
$today_refill   = (float) ($todayTotals['total_refill'] ?? 0);

// =====================
// WEEK
// =====================
$startOfWeek = date('Y-m-d', strtotime('monday this week'));
$endOfWeek   = date('Y-m-d', strtotime('sunday this week'));
$weekTotals = getTotals($pdo, $user_id, $startOfWeek, $endOfWeek);
$weekly_spending = (float) ($weekTotals['total_expense'] ?? 0);
$weekly_refill   = (float) ($weekTotals['total_refill'] ?? 0);

// =====================
// MONTH
// =====================
$startOfMonth = date('Y-m-01');
$endOfMonth   = date('Y-m-t');
$monthTotals = getTotals($pdo, $user_id, $startOfMonth, $endOfMonth);
$monthly_spending = (float) ($monthTotals['total_expense'] ?? 0);
$monthly_refill   = (float) ($monthTotals['total_refill'] ?? 0);

// =====================
// TOTAL (ALL TIME)
// =====================
$totalTotals = getTotals($pdo, $user_id);
$total_expenses = (float) ($totalTotals['total_expense'] ?? 0);
$total_refills  = (float) ($totalTotals['total_refill'] ?? 0);
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="dashboard">
    <h1>Dashboard</h1>

    <!-- ALERTS -->
    <?php if ($current_balance < 500): ?>
        <div class="alert alert-error" id="alert-low-balance">
            ⚠️ Low balance! You have KES <?= number_format($current_balance,2) ?> only.
        </div>
    <?php endif; ?>

    <?php if ($today_spending > ($current_balance * 0.5)): ?>
        <div class="alert alert-error" id="alert-high-spending">
            🚨 High spending today! You tried to spend KES <?= number_format($today_spending,2) ?>.
        </div>
    <?php endif; ?>

    <div class="cards">

        <!-- Current Balance -->
        <div class="card balance">
            <h3>Current Balance</h3>
            <p>KES <?= number_format($current_balance, 2); ?></p>
        </div>

        <!-- Today -->
        <div class="card">
            <h3>Today's Spending</h3>
            <p>KES <?= number_format($today_spending, 2); ?></p>
        </div>

        <div class="card">
            <h3>Today's Refills</h3>
            <p>KES <?= number_format($today_refill, 2); ?></p>
        </div>

        <!-- Week -->
        <div class="card">
            <h3>Weekly Spending</h3>
            <p>KES <?= number_format($weekly_spending, 2); ?></p>
        </div>

        <div class="card">
            <h3>Weekly Refills</h3>
            <p>KES <?= number_format($weekly_refill, 2); ?></p>
        </div>

        <!-- Month -->
        <div class="card">
            <h3>Monthly Spending</h3>
            <p>KES <?= number_format($monthly_spending, 2); ?></p>
        </div>

        <div class="card">
            <h3>Monthly Refills</h3>
            <p>KES <?= number_format($monthly_refill, 2); ?></p>
        </div>

        <!-- All-time totals -->
        <div class="card danger">
            <h3>Total Expenses</h3>
            <p>KES <?= number_format($total_expenses, 2); ?></p>
        </div>

        <div class="card balance">
            <h3>Total Refills</h3>
            <p>KES <?= number_format($total_refills, 2); ?></p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Auto-hide alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 1s';
            alert.style.opacity = 0;
            setTimeout(() => alert.remove(), 1000);
        }, 5000);
    });
});
</script>

<?php include '../includes/footer.php'; ?>
