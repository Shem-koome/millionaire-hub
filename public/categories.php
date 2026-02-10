<?php
define('APP_STARTED', true);

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/middleware/auth.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Fetch user categories (not deleted)
$stmt = $pdo->prepare("
    SELECT id, name, color, icon 
    FROM categories 
    WHERE user_id = ? AND is_deleted = 0
    ORDER BY name ASC
");
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="container">
    <h1>Categories</h1>

    <!-- ADD CATEGORY -->
    <form action="../app/actions/categories/category_actions.php" method="POST" class="card">
        <h3>Add Category</h3>

        <input type="hidden" name="action" value="add">

        <input type="text" name="name" placeholder="Category name" required>

        <input type="color" name="color">

        <input type="text" name="icon" placeholder="Icon (optional)">

        <button type="submit">Add Category</button>
    </form>

    <!-- CATEGORY LIST -->
    <div class="card">
        <h3>Your Categories</h3>

        <?php if (empty($categories)): ?>
            <p>No categories yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Color</th>
                        <th>Icon</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= htmlspecialchars($cat['name']) ?></td>
                            <td>
                                <span style="
                                    display:inline-block;
                                    width:20px;
                                    height:20px;
                                    background:<?= $cat['color'] ?: '#ccc' ?>;
                                    border-radius:50%;">
                                </span>
                            </td>
                            <td><?= htmlspecialchars($cat['icon']) ?></td>
                            <td>
                                <form action="../app/actions/categories/category_actions.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                    <button type="submit" class="danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
