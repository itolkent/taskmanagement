<?php

namespace App\Middleware;

use App\Core\DB;
use App\Middleware\AuthMiddleware;
use PDO;

class ProjectAccessMiddleware
{
   
    public static function requireRole(int $projectId, array $allowedRoles): array
    {
        $user = AuthMiddleware::handle();

        $pdo = DB::conn();
        $stmt = $pdo->prepare("
            SELECT role
            FROM project_user
            WHERE project_id = :project_id
              AND user_id = :user_id
            LIMIT 1
        ");

        $stmt->execute([
            'project_id' => $projectId,
            'user_id' => $user['id']
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            http_response_code(403);
            echo json_encode(['error' => 'You are not a member of this project']);
            exit;
        }

        $role = $row['role'];

        if (!in_array($role, $allowedRoles, true)) {
            http_response_code(403);
            echo json_encode(['error' => 'Insufficient permissions']);
            exit;
        }

        return [
            'user' => $user,
            'role' => $role
        ];
    }
}