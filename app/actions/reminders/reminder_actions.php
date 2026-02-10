<?php
define('APP_STARTED', true);

require_once '../../config/database.php';
require_once '../../middleware/auth.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

function respond($ok = true, $extra = []) {
    echo json_encode(array_merge(['success' => $ok], $extra));
    exit();
}

/* ================= NOTES ================= */
if ($action === 'add_note') {
    $content = trim($_POST['content'] ?? '');
    if(!$content) respond(false, ['message'=>'Note content cannot be empty']);

    $category_id = $_POST['category_id'] ?: null;

    $stmt = $pdo->prepare("INSERT INTO notes (user_id, content, category_id, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$user_id, $content, $category_id]);

    respond(true);
}

if ($action === 'edit_note') {
    $content = trim($_POST['content'] ?? '');
    if(!$content) respond(false, ['message'=>'Note content cannot be empty']);

    $stmt = $pdo->prepare("UPDATE notes SET content = ?, category_id = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$content, $_POST['category_id'] ?: null, $_POST['id'], $user_id]);

    respond(true);
}

if ($action === 'delete_note') {
    $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['id'], $user_id]);

    respond(true);
}

/* ================= REMINDERS ================= */
if ($action === 'add_reminder') {
    $title = trim($_POST['title'] ?? '');
    $remind_at = trim($_POST['remind_at'] ?? '');

    if(!$title) respond(false, ['message'=>'Reminder title is required']);
    if(!$remind_at) respond(false, ['message'=>'Reminder date & time is required']);

    $stmt = $pdo->prepare("
        INSERT INTO reminders (user_id, title, description, category_id, note_id, remind_at, status)
        VALUES (?, ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([
        $user_id,
        $title,
        $_POST['description'] ?: null,
        $_POST['category_id'] ?: null,
        $_POST['note_id'] ?: null,
        $remind_at
    ]);

    respond(true);
}

// EDIT REMINDER
if ($action === 'edit_reminder') {
    $title = trim($_POST['title'] ?? '');
    $remind_at = trim($_POST['remind_at'] ?? '');

    if(!$title) respond(false, ['message'=>'Reminder title is required']);
    if(!$remind_at) respond(false, ['message'=>'Reminder date & time is required']);

    $stmt = $pdo->prepare("
        UPDATE reminders
        SET title = ?, description = ?, category_id = ?, note_id = ?, remind_at = ?
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([
        $title,
        $_POST['description'] ?: null,
        $_POST['category_id'] ?: null,
        $_POST['note_id'] ?: null,
        $remind_at,
        $_POST['id'],
        $user_id
    ]);

    respond(true);
}

if ($action === 'mark_done') {
    $stmt = $pdo->prepare("UPDATE reminders SET status = 'done' WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['id'], $user_id]);

    respond(true, ['status' => 'done']);
}

if ($action === 'delete_reminder') {
    $stmt = $pdo->prepare("DELETE FROM reminders WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['id'], $user_id]);

    respond(true);
}

respond(false, ['message' => 'Unknown action']);
