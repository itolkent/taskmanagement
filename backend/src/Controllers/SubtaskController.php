<?php

namespace App\Controllers;

use App\Core\DB;
use App\Middleware\AuthMiddleware;
use PDO;

class SubtaskController
{
    public static function index(array $params): array
    {
        AuthMiddleware::handle();

        $taskId = (int) $params['taskId'];

        $stmt = DB::conn()->prepare("
            SELECT *
            FROM subtasks
            WHERE task_id = ?
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([$taskId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function store(array $params): array
    {
        $user = AuthMiddleware::handle();

        $taskId = (int) $params['taskId'];
        $input = json_decode(file_get_contents('php://input'), true);

        $title = trim($input['title'] ?? '');

        if ($title === '') {
            http_response_code(422);
            return ['error' => 'Title is required'];
        }
        $stmt = DB::conn()->prepare("
            INSERT INTO subtasks (task_id, title, is_completed, sort_order, created_at, updated_at)
            VALUES (?, ?, 0, 0, NOW(), NOW())
        ");
        $stmt->execute([$taskId, $title]);

        $id = DB::conn()->lastInsertId();

        if (function_exists('logActivity')) {
            logActivity($taskId, $user['id'], "added subtask '{$title}'");
        }

        return [
            'id' => (int) $id,
            'task_id' => $taskId,
            'title' => $title,
            'is_completed' => 0,
            'sort_order' => 0
        ];
    }

    public static function update(array $params): array
    {
        $user = AuthMiddleware::handle();

        $id = (int) $params['subtaskId'];
        $input = json_decode(file_get_contents('php://input'), true);

        $stmt = DB::conn()->prepare("SELECT task_id FROM subtasks WHERE id = ?");
        $stmt->execute([$id]);
        $subtask = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$subtask) {
            http_response_code(404);
            return ['error' => 'Subtask not found'];
        }

        $stmt = DB::conn()->prepare("
            UPDATE subtasks
            SET title = ?, is_completed = ?, sort_order = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $input['title'],
            $input['is_completed'],
            $input['sort_order'],
            $id
        ]);

        $action = $input['is_completed'] ? "completed a subtask" : "updated a subtask";
        if (function_exists('logActivity')) {
            logActivity($subtask['task_id'], $user['id'], $action);
        }

        return ['success' => true];
    }
    public static function destroy(array $params): array
    {
        $user = AuthMiddleware::handle();

        $id = (int) $params['subtaskId'];

        $stmt = DB::conn()->prepare("SELECT task_id, title FROM subtasks WHERE id = ?");
        $stmt->execute([$id]);
        $subtask = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$subtask) {
            http_response_code(404);
            return ['error' => 'Subtask not found'];
        }

        $stmt = DB::conn()->prepare("DELETE FROM subtasks WHERE id = ?");
        $stmt->execute([$id]);

        if (function_exists('logActivity')) {
            logActivity($subtask['task_id'], $user['id'], "deleted subtask '{$subtask['title']}'");
        }

        return ['success' => true];
    }
}