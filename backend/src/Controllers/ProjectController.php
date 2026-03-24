<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Project;
use App\Core\DB;
use DateTime;
use PDO;

class ProjectController
{
    public static function index(): array
    {
        $user = AuthMiddleware::handle();
        return Project::forUser($user['id']);
    }

    public static function store(): array
    {
        $user = AuthMiddleware::handle();
        $input = json_decode(file_get_contents('php://input'), true);

        $name = trim($input['name'] ?? '');
        $visibility = $input['visibility'] ?? 'private';
        $description = $input['description'] ?? null;

        if ($name === '') {
            http_response_code(422);
            return ['error' => 'Name is required'];
        }

        $validVisibility = ['public', 'private', 'team'];
        if (!in_array($visibility, $validVisibility, true)) {
            http_response_code(422);
            return ['error' => 'Invalid visibility'];
        }

        $projectId = Project::createProject([
            'name' => $name,
            'description' => $description,
            'visibility' => $visibility,
            'archived' => 0,
        ], $user['id']);

        return Project::find($projectId);
    }

    public static function show(array $params): array
    {
        $user = AuthMiddleware::handle();

        $projectId = (int) $params['projectId'];
        $project = Project::find($projectId);

        if (!$project) {
            http_response_code(404);
            return ['error' => 'Project not found'];
        }
        $stmt = DB::conn()->prepare("
            SELECT 1
            FROM project_user
            WHERE project_id = :pid AND user_id = :uid
        ");
        $stmt->execute([
            'pid' => $projectId,
            'uid' => $user['id']
        ]);

        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            http_response_code(403);
            return ['error' => 'Forbidden'];
        }

        return $project;
    }
    public static function update(array $params): array
    {
        $user = AuthMiddleware::handle(['admin', 'project_manager']);

        $projectId = (int) $params['projectId'];
        $project = Project::find($projectId);

        if (!$project) {
            http_response_code(404);
            return ['error' => 'Project not found'];
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $data = [];
        foreach (['name', 'description', 'visibility', 'archived'] as $field) {
            if (array_key_exists($field, $input)) {
                $data[$field] = $input[$field];
            }
        }

        $data['updated_at'] = (new DateTime())->format('Y-m-d H:i:s');

        Project::update($projectId, $data);

        return Project::find($projectId);
    }

    public static function destroy(array $params): array
    {
        $user = AuthMiddleware::handle(['admin']);

        $projectId = (int) $params['projectId'];

        Project::delete($projectId);

        return ['status' => 'deleted'];
    }
}