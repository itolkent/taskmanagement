<?php

namespace App\Controllers;

use App\Core\DB;
use App\Middleware\AuthMiddleware;
use PDO;

class TaskListController
{
    /**
     * GET /api/v1/boards/{boardId}/lists
     * (Not used by frontend anymore — BoardController@getLists() is used instead)
     */
    public static function index(array $params): array
    {
        AuthMiddleware::handle();

        $boardId = (int) $params['boardId'];

        $stmt = DB::conn()->prepare("
            SELECT id, board_id, name, sort_order
            FROM task_lists
            WHERE board_id = ?
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([$boardId]);

        $lists = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Frontend expects "task_lists" array
        foreach ($lists as &$list) {
            $list['task_lists'] = [];
        }

        return $lists;
    }
    public static function create(array $params): array
    {
        AuthMiddleware::handle();

        $boardId = (int) $params['boardId'];
        $input = json_decode(file_get_contents("php://input"), true);

        $name = trim($input['name'] ?? '');

        if ($name === '') {
            http_response_code(422);
            return ['error' => 'List name is required'];
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
            'task_lists' => []
        ];
    }
    public static function update(array $params): array
    {
        AuthMiddleware::handle();

        $listId = (int) $params['listId'];
        $input = json_decode(file_get_contents("php://input"), true);

        $name = trim($input['name'] ?? '');

        if ($name === '') {
            http_response_code(422);
            return ['error' => 'List name is required'];
        }

        $stmt = DB::conn()->prepare("
            UPDATE task_lists
            SET name = ?, updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([$name, $listId]);

        return [
            'id' => $listId,
            'name' => $name
        ];
    }
}