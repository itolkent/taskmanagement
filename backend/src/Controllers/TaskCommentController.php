<?php

namespace App\Controllers;

use App\Core\DB;
use App\Middleware\AuthMiddleware;
use PDO;

class TaskCommentController
{
    public static function index(array $params): array
    {
        AuthMiddleware::handle();

        $taskId = (int) $params['taskId'];

        $stmt = DB::conn()->prepare("
            SELECT tc.*, u.name AS user_name, u.avatar
            FROM task_comments tc
            JOIN users u ON u.id = tc.user_id
            WHERE tc.task_id = ?
            ORDER BY tc.created_at ASC
        ");
        $stmt->execute([$taskId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function store(array $params): array
    {
        $user = AuthMiddleware::handle();
        $taskId = (int) $params['taskId'];

        $input = json_decode(file_get_contents('php://input'), true);
        $comment = trim($input['comment'] ?? '');

        if ($comment === '') {
            http_response_code(422);
            return ['error' => 'Comment is required'];
        }

        $stmt = DB::conn()->prepare("
            INSERT INTO task_comments (task_id, user_id, comment, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$taskId, $user['id'], $comment]);

        $id = DB::conn()->lastInsertId();

        if (function_exists('logActivity')) {
            logActivity($taskId, $user['id'], "added a comment");
        }

        return [
            'id' => (int) $id,
            'task_id' => $taskId,
            'user_id' => $user['id'],
            'comment' => $comment,
            'created_at' => date('Y-m-d H:i:s'),
            'user_name' => $user['name'] ?? null,
            'avatar' => $user['avatar'] ?? null
        ];
    }

    public static function destroy(array $params): array
    {
        AuthMiddleware::handle();

        $commentId = (int) $params['commentId'];

        $stmt = DB::conn()->prepare("
            DELETE FROM task_comments
            WHERE id = ?
        ");
        $stmt->execute([$commentId]);

        return ['success' => true];
    }
}