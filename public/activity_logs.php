<?php
define('APP_STARTED', true);
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/middleware/auth.php';

// Safety check for user session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Fetch logs for the current user
$stmt = $pdo->prepare("
    SELECT * FROM activity_logs 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 50
");
$stmt->execute([$user_id]);
$logs = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-wrapper">
    <div class="welcome-container" style="max-width: 1000px; text-align: left;">
        <h1>Activity <span>Logs</span></h1>
        <p>A history of your recent actions.</p>

        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr style="background: #1E3A8A; color: #D4AF37; text-align: left;">
                    <th style="padding: 15px; border-bottom: 2px solid #D4AF37;">Action</th>
                    <th style="padding: 15px; border-bottom: 2px solid #D4AF37;">Description</th>
                    <th style="padding: 15px; border-bottom: 2px solid #D4AF37;">Date & Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="3" style="padding: 30px; text-align: center; color: #666;">
                            No activity recorded yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px;">
                                <span style="font-weight: 600; color: #1E3A8A;">
                                    <?= htmlspecialchars($log['action_type']) ?>
                                </span>
                            </td>
                            <td style="padding: 15px; color: #444;">
                                <?= htmlspecialchars($log['description'] ?? 'No details provided.') ?>
                            </td>
                            <td style="padding: 15px; font-size: 0.85rem; color: #888;">
                                <?= date('d M Y, h:i A', strtotime($log['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>