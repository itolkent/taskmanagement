<?php

namespace App\Models;

use App\Core\Model;
use App\Core\DB;
use DateTime;
use PDO;

class Task extends Model
{
    protected static string $table = 'tasks';

    public static function forBoard(int $boardId): array
    {
        $pdo = DB::conn();

        $sql = "
            SELECT t.*, tl.name AS list_name
            FROM tasks t
            JOIN task_lists tl ON tl.id = t.task_list_id
            WHERE tl.board_id = :boardId
            ORDER BY tl.sort_order ASC, t.sort_order ASC, t.id ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['boardId' => $boardId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function createTask(array $data, int $creatorId): int
    {
        $now = (new DateTime())->format('Y-m-d H:i:s');

        $data['created_by'] = $creatorId;
        $data['created_at'] = $now;
        $data['updated_at'] = $now;

        return self::create($data);
    }

    public static function find(int $id): ?array
    {
        $pdo = DB::conn();

        $stmt = $pdo->prepare("
            SELECT *
            FROM " . static::$table . "
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}