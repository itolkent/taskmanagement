<?php
class ActivityController
{
    public function index($taskId)
    {
        global $pdo;

        $stmt = $pdo->prepare("
            SELECT ta.*, u.name AS user_name
            FROM task_activity ta
            JOIN users u ON u.id = ta.user_id
            WHERE task_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$taskId]);

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
} ?>