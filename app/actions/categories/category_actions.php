<?php
define('APP_STARTED', true);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/logger.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../../public/categories.php');
    exit;
}

$action = $_POST['action'] ?? '';

/*
|--------------------------------------------------------------------------
| ADD CATEGORY
|--------------------------------------------------------------------------
*/
if ($action === 'add') {

    $name  = trim($_POST['name']);
    $color = $_POST['color'] ?? null;
    $icon  = trim($_POST['icon']) ?: null;

    if ($name === '') {
        header('Location: ../../../public/categories.php');
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO categories (user_id, name, color, icon)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $name, $color, $icon]);

    logActivity($pdo, $user_id, 'CATEGORY_ADD', "Added category: {$name}");

    header('Location: ../../../public/categories.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| DELETE CATEGORY (SOFT DELETE)
|--------------------------------------------------------------------------
*/
if ($action === 'delete') {

    $id = (int) $_POST['id'];

    $stmt = $pdo->prepare("
        UPDATE categories 
        SET is_deleted = 1 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$id, $user_id]);

    logActivity($pdo, $user_id, 'CATEGORY_DELETE', "Deleted category ID: {$id}");

    header('Location: ../../../public/categories.php');
    exit;
}

header('Location: ../../../public/categories.php');
exit;
