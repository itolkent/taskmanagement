<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Core\DB;
use PDO;

class ReportController
{
    public static function overview(): array
    {
        $user = AuthMiddleware::handle();
        $pdo = DB::conn();

        $sql = "
            SELECT
                SUM(CASE WHEN t.status = 'not_started' THEN 1 ELSE 0 END) AS not_started,
                SUM(CASE WHEN t.status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress,
                SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) AS completed,
                SUM(CASE WHEN t.status = 'on_hold' THEN 1 ELSE 0 END) AS on_hold
            FROM tasks t
            JOIN task_lists tl ON tl.id = t.task_list_id
            JOIN boards b ON b.id = tl.board_id
            JOIN projects p ON p.id = b.project_id
            JOIN project_user pu ON pu.project_id = p.id
            WHERE pu.user_id = :uid
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['uid' => $user['id']]);
        $counts = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'status_counts' => $counts
        ];
    }

    public static function overdue(): array
    {
        $user = AuthMiddleware::handle();
        $pdo = DB::conn();

        $sql = "
            SELECT t.*
            FROM tasks t
            JOIN task_lists tl ON tl.id = t.task_list_id
            JOIN boards b ON b.id = tl.board_id
            JOIN projects p ON p.id = b.project_id
            JOIN project_user pu ON pu.project_id = p.id
            WHERE pu.user_id = :uid
              AND t.due_date IS NOT NULL
              AND t.due_date < NOW()
              AND t.status <> 'completed'
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['uid' => $user['id']]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function completedPerUser(): array
    {
        $user = AuthMiddleware::handle();
        $pdo = DB::conn();

        $sql = "
            SELECT u.id, u.name, COUNT(t.id) AS completed_tasks
            FROM users u
            JOIN tasks t ON t.assignee_id = u.id
            JOIN task_lists tl ON tl.id = t.task_list_id
            JOIN boards b ON b.id = tl.board_id
            JOIN projects p ON p.id = b.project_id
            JOIN project_user pu ON pu.project_id = p.id
            WHERE pu.user_id = :uid
              AND t.status = 'completed'
            GROUP BY u.id, u.name
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['uid' => $user['id']]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}