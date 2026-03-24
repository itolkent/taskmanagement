<?php

namespace App\Controllers;

use App\Core\DB;
use App\Middleware\AuthMiddleware;
use PDO;

class TaskLabelController
{
    public static function getTaskLabels(array $params): array
    {
        AuthMiddleware::handle();

        $taskId = (int) $params['taskId'];

        $stmt = DB::conn()->prepare("
            SELECT l.*
            FROM task_label tl
            JOIN labels l ON l.id = tl.label_id
            WHERE tl.task_id = ?
        ");
        $stmt->execute([$taskId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function attach(array $params): array
    {
        AuthMiddleware::handle();

        $taskId = (int) $params['taskId'];
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['label_id'])) {
            http_response_code(422);
            return ['error' => 'label_id is required'];
        }

        $labelId = (int) $input['label_id'];

        $stmt = DB::conn()->prepare("
            INSERT IGNORE INTO task_label (task_id, label_id)
            VALUES (?, ?)
        ");
        $stmt->execute([$taskId, $labelId]);

        return ['success' => true];
    }

    public static function detach(array $params): array
    {
        AuthMiddleware::handle();

        $taskId = (int) $params['taskId'];
        $labelId = (int) $params['labelId'];

        $stmt = DB::conn()->prepare("
            DELETE FROM task_label
            WHERE task_id = ? AND label_id = ?
        ");
        $stmt->execute([$taskId, $labelId]);

        return ['success' => true];
    }
}