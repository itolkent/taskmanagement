<?php

namespace App\Controllers;

use App\Core\DB;
use App\Middleware\AuthMiddleware;
use PDO;

class ListController
{
    public static function create(array $params): array
    {
        AuthMiddleware::handle();

        $boardId = (int) $params['boardId'];

        $input = json_decode(file_get_contents("php://input"), true);
        $name = trim($input['name'] ?? '');

        if ($name === '') {
            http_response_code(422);
            return ['error' => 'List name required'];
        }

        $stmt = DB::conn()->prepare("
            INSERT INTO task_lists (board_id, name, sort_order, created_at, updated_at)
            SELECT ?, ?, COALESCE(MAX(sort_order), 0) + 1, NOW(), NOW()
            FROM task_lists
            WHERE board_id = ?
        ");

        $stmt->execute([$boardId, $name, $boardId]);

        $id = DB::conn()->lastInsertId();

        return [
            'id' => (int) $id,
            'board_id' => $boardId,
            'name' => $name,
            'sort_order' => 0,
            'tasks' => []
        ];
    }
}