<?php
function logActivity($taskId, $userId, $action)
{
    global $pdo;

    $stmt = $pdo->prepare("
        INSERT INTO task_activity (task_id, user_id, action)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$taskId, $userId, $action]);
}
?>