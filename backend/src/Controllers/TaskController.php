<?php

namespace App\Controllers;

use App\Core\DB;
use App\Middleware\AuthMiddleware;
use App\Models\Task;
use DateTime;
use PDO;

class TaskController
{
    public static function index(array $params): array
    {
        AuthMiddleware::handle();

        $listId = (int) $params['listId'];

        $stmt = DB::conn()->prepare("
            SELECT id, name, sort_order
            FROM task_lists
            WHERE id = ?
        ");
        $stmt->execute([$listId]);
        $list = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$list) {
            http_response_code(404);
            return ['error' => 'List not found'];
        }

        $stmt = DB::conn()->prepare("
            SELECT *
            FROM tasks
            WHERE task_list_id = ?
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([$listId]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'id' => $list['id'],
            'name' => $list['name'],
            'sort_order' => $list['sort_order'],
            'tasks' => $tasks,
        ];
    }

    public static function store(array $params): array
    {
        $user = AuthMiddleware::handle();

        $listId = (int) $params['listId'];
        $input = json_decode(file_get_contents("php://input"), true) ?? [];

        $title = trim($input['title'] ?? '');

        if ($title === '') {
            http_response_code(422);
            return ['error' => 'Title is required'];
        }

        $stmt = DB::conn()->prepare("
            SELECT COALESCE(MAX(sort_order), 0) + 1 AS next_order
            FROM tasks
            WHERE task_list_id = ?
        ");
        $stmt->execute([$listId]);
        $nextOrder = (int) $stmt->fetchColumn();

        $taskId = Task::createTask([
            'task_list_id' => $listId,
            'title' => $title,
            'description' => $input['description'] ?? null,
            'status' => $input['status'] ?? 'not_started',
            'priority' => $input['priority'] ?? 'medium',
            'assignee_id' => $input['assignee_id'] ?? null,
            'due_date' => $input['due_date'] ?? null,
            'estimate_minutes' => $input['estimate_minutes'] ?? null,
            'spent_minutes' => 0,
            'sort_order' => $nextOrder,
        ], $user['id']);

        $task = Task::find($taskId);

        $task['task_list_id'] = $listId;

        return $task;
    }
    public static function show(array $params): array
    {
        AuthMiddleware::handle();

        $taskId = (int) $params['taskId'];
        $task = Task::find($taskId);

        if (!$task) {
            http_response_code(404);
            return ['error' => 'Task not found'];
        }

        return $task;
    }

    public static function update(array $params): array
    {
        AuthMiddleware::handle();

        $taskId = (int) $params['taskId'];
        $task = Task::find($taskId);

        if (!$task) {
            http_response_code(404);
            return ['error' => 'Task not found'];
        }

        $input = json_decode(file_get_contents("php://input"), true) ?? [];

        $fields = [
            'title',
            'description',
            'status',
            'priority',
            'assignee_id',
            'due_date',
            'estimate_minutes',
            'task_list_id',
            'sort_order',
        ];

        $data = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $input)) {
                $data[$field] = $input[$field];
            }
        }

        $data['updated_at'] = (new DateTime())->format('Y-m-d H:i:s');

        Task::update($taskId, $data);

        $task = Task::find($taskId);

        if ($task['assignee_id']) {
            $stmt = DB::conn()->prepare("
            SELECT id AS user_id, name, avatar_url
            FROM users
            WHERE id = ?
        ");
            $stmt->execute([$task['assignee_id']]);
            $task['assignee'] = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $task['assignee'] = null;
        }

        return $task;
    }

    public static function destroy(array $params): array
    {
        AuthMiddleware::handle();

        $taskId = (int) $params['taskId'];
        Task::delete($taskId);

        return ['status' => 'deleted'];
    }
}