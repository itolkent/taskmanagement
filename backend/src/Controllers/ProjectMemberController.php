<?php

namespace App\Controllers;

use App\Core\DB;
use App\Middleware\AuthMiddleware;
use App\Middleware\ProjectAccessMiddleware;
use App\Models\ProjectInvite;
use PDO;

class ProjectMemberController
{
    public static function index(array $params): array
    {
        $user = AuthMiddleware::handle();
        $projectId = (int) $params['projectId'];

        ProjectAccessMiddleware::requireRole($projectId, [
            'owner',
            'admin',
            'team_member',
            'project_manager'
        ]);

        $pdo = DB::conn();

        $stmt = $pdo->prepare("
        SELECT pu.user_id, pu.role, u.name, u.email
        FROM project_user pu
        JOIN users u ON u.id = pu.user_id
        WHERE pu.project_id = :project_id
        ORDER BY FIELD(pu.role, 'owner','admin','team_member','project_manager'), u.name
    ");
        $stmt->execute(['project_id' => $projectId]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pendingInvites = ProjectInvite::pendingForProject($projectId);

        $stmt = $pdo->prepare("
        SELECT role 
        FROM project_user 
        WHERE project_id = :project_id AND user_id = :user_id
    ");
        $stmt->execute([
            'project_id' => $projectId,
            'user_id' => $user['id']
        ]);
        $myRole = $stmt->fetch(PDO::FETCH_ASSOC)['role'] ?? null;

        return [
            'members' => $members,
            'pending_invites' => $pendingInvites,
            'myRole' => $myRole
        ];
    }

    public static function store(array $params): array
    {
        $authUser = AuthMiddleware::handle();
        $projectId = (int) $params['projectId'];

        ProjectAccessMiddleware::requireRole($projectId, ['owner', 'admin', 'project_manager']);
        $input = json_decode(file_get_contents("php://input"), true);
        $userId = $input['user_id'] ?? null;
        $role = $input['role'] ?? 'team_member';

        if (!$userId) {
            http_response_code(422);
            return ['error' => 'user_id is required'];
        }

        $pdo = DB::conn();

        $stmt = $pdo->prepare("
        INSERT INTO project_user (project_id, user_id, role)
        VALUES (:project_id, :user_id, :role)
    ");

        $stmt->execute([
            'project_id' => $projectId,
            'user_id' => $userId,
            'role' => $role
        ]);

        return [
            'team_member' => [
                'id' => $userId,
                'role' => $role
            ]
        ];
    }

    public static function update(array $params): array
    {
        $user = AuthMiddleware::handle();
        $projectId = (int) $params['projectId'];
        $targetUserId = (int) $params['userId'];

        ProjectAccessMiddleware::requireRole($projectId, ['owner', 'admin', 'project_manager']);

        $input = json_decode(file_get_contents("php://input"), true);
        $role = $input['role'] ?? '';

        if (!in_array($role, ['owner', 'admin', 'team_member', 'project_manager'], true)) {
            http_response_code(422);
            return ['error' => 'Invalid role'];
        }

        $pdo = DB::conn();

        if ($role !== 'owner') {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) AS owners
                FROM project_user
                WHERE project_id = :project_id AND role = 'owner'
            ");
            $stmt->execute(['project_id' => $projectId]);
            $owners = (int) $stmt->fetch(PDO::FETCH_ASSOC)['owners'];

            if ($owners <= 1) {
                $stmt = $pdo->prepare("
                    SELECT role
                    FROM project_user
                    WHERE project_id = :project_id AND user_id = :user_id
                ");
                $stmt->execute([
                    'project_id' => $projectId,
                    'user_id' => $targetUserId
                ]);

                $currentRole = $stmt->fetch(PDO::FETCH_ASSOC)['role'] ?? null;

                if ($currentRole === 'owner') {
                    http_response_code(403);
                    return ['error' => 'Cannot remove the only owner'];
                }
            }
        }
        $stmt = $pdo->prepare("
            UPDATE project_user
            SET role = :role
            WHERE project_id = :project_id AND user_id = :user_id
        ");
        $stmt->execute([
            'role' => $role,
            'project_id' => $projectId,
            'user_id' => $targetUserId
        ]);

        return ['status' => 'updated', 'user_id' => $targetUserId, 'role' => $role];
    }

    public static function destroy(array $params): array
    {
        $user = AuthMiddleware::handle();
        $projectId = (int) $params['projectId'];
        $targetUserId = (int) $params['userId'];

        ProjectAccessMiddleware::requireRole($projectId, ['owner', 'admin', 'project_manager']);

        $pdo = DB::conn();

        $stmt = $pdo->prepare("
            SELECT role
            FROM project_user
            WHERE project_id = :project_id AND user_id = :user_id
        ");
        $stmt->execute([
            'project_id' => $projectId,
            'user_id' => $targetUserId
        ]);

        $role = $stmt->fetch(PDO::FETCH_ASSOC)['role'] ?? null;

        if ($role === 'owner') {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) AS owners
                FROM project_user
                WHERE project_id = :project_id AND role = 'owner'
            ");
            $stmt->execute(['project_id' => $projectId]);
            $owners = (int) $stmt->fetch(PDO::FETCH_ASSOC)['owners'];

            if ($owners <= 1) {
                http_response_code(403);
                return ['error' => 'Cannot remove the only owner'];
            }
        }
        $stmt = $pdo->prepare("
            DELETE FROM project_user
            WHERE project_id = :project_id AND user_id = :user_id
        ");
        $stmt->execute([
            'project_id' => $projectId,
            'user_id' => $targetUserId
        ]);

        return ['status' => 'removed', 'user_id' => $targetUserId];
    }
}