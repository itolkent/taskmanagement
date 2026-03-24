<?php

namespace App\Controllers;

use App\Core\DB;
use App\Middleware\AuthMiddleware;
use PDO;
use PDOException;

class BoardController
{
    public static function getBoardsByProject($params)
    {
        AuthMiddleware::handle();

        $projectId = (int) $params['projectId'];

        try {
            $stmt = DB::conn()->prepare("
                SELECT *
                FROM boards
                WHERE project_id = ?
                ORDER BY sort_order ASC, id ASC
            ");
            $stmt->execute([$projectId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            http_response_code(500);
            return [
                'error' => 'Failed to fetch boards',
                'details' => $e->getMessage()
            ];
        }
    }

    public static function create($params)
    {
        AuthMiddleware::handle();

        $projectId = (int) $params['projectId'];
        $input = json_decode(file_get_contents('php://input'), true);

        $name = trim($input['name'] ?? '');

        if ($name === '') {
            http_response_code(422);
            return ['error' => 'Board name is required'];
        }

        try {
            $db = DB::conn();

            $check = $db->prepare("SELECT id FROM projects WHERE id = ?");
            $check->execute([$projectId]);

            if (!$check->fetch(PDO::FETCH_ASSOC)) {
                http_response_code(404);
                return ['error' => 'Project not found'];
            }

            $stmt = $db->prepare("
                INSERT INTO boards (project_id, name, sort_order, created_at, updated_at)
                SELECT ?, ?, COALESCE(MAX(sort_order), 0) + 1, NOW(), NOW()
                FROM boards
                WHERE project_id = ?
            ");
            $stmt->execute([$projectId, $name, $projectId]);

            $id = $db->lastInsertId();

            return [
                'id' => (int) $id,
                'project_id' => $projectId,
                'name' => $name,
                'sort_order' => 0
            ];

        } catch (PDOException $e) {
            http_response_code(500);
            return [
                'error' => 'Failed to create board',
                'details' => $e->getMessage()
            ];
        }
    }

    public static function getBoard($params)
    {
        AuthMiddleware::handle();

        $boardId = (int) $params['boardId'];

        try {
            $stmt = DB::conn()->prepare("SELECT * FROM boards WHERE id = ?");
            $stmt->execute([$boardId]);

            $board = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$board) {
                http_response_code(404);
                return ['error' => 'Board not found'];
            }

            return $board;

        } catch (PDOException $e) {
            http_response_code(500);
            return [
                'error' => 'Failed to fetch board',
                'details' => $e->getMessage()
            ];
        }
    }

    public static function update($params)
    {
        AuthMiddleware::handle();

        $boardId = (int) $params['boardId'];
        $input = json_decode(file_get_contents('php://input'), true);

        $name = trim($input['name'] ?? '');

        if ($name === '') {
            http_response_code(422);
            return ['error' => 'Board name is required'];
        }

        try {
            $stmt = DB::conn()->prepare("
                UPDATE boards
                SET name = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$name, $boardId]);

            return [
                'id' => $boardId,
                'name' => $name
            ];

        } catch (PDOException $e) {
            http_response_code(500);
            return [
                'error' => 'Failed to update board',
                'details' => $e->getMessage()
            ];
        }
    }

    public static function delete($params)
    {
        AuthMiddleware::handle();

        $boardId = (int) $params['boardId'];

        try {
            $stmt = DB::conn()->prepare("DELETE FROM boards WHERE id = ?");
            $stmt->execute([$boardId]);

            return ['success' => true];

        } catch (PDOException $e) {
            http_response_code(500);
            return [
                'error' => 'Failed to delete board',
                'details' => $e->getMessage()
            ];
        }
    }

    public static function getLists($params)
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

        foreach ($lists as &$list) {
            $stmt = DB::conn()->prepare("
                SELECT *
                FROM tasks
                WHERE task_list_id = ?
                ORDER BY sort_order ASC, id ASC
            ");
            $stmt->execute([$list['id']]);
            $list['task_lists'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $lists;
    }
}