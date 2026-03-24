<?php

namespace App\Models;

use App\Core\DB;
use PDO;

class TaskList
{
    public static function allForBoard(int $boardId): array
    {
        $stmt = DB::conn()->prepare("
            SELECT id, board_id, name, sort_order
            FROM task_lists
            WHERE board_id = ?
            ORDER BY sort_order ASC
        ");
        $stmt->execute([$boardId]);

        $lists = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($lists as &$list) {
            $list['tasks'] = [];
        }

        return $lists;
    }

    public static function create(int $boardId, string $name): array
    {
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

    public static function update(int $listId, string $name): array
    {
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

    public static function delete(int $listId): bool
    {
        $stmt = DB::conn()->prepare("DELETE FROM task_lists WHERE id = ?");
        return $stmt->execute([$listId]);
    }
}