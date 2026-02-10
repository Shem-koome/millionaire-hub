<?php

function logActivity(PDO $pdo, int $user_id, string $action, string $description = null)
{
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, action_type, description)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$user_id, $action, $description]);
}
