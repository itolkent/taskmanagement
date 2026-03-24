<?php

namespace App\Controllers;

use App\Core\DB;
use App\Middleware\AuthMiddleware;
use App\Middleware\ProjectAccessMiddleware;
use App\Models\ProjectInvite;
use PDO;

class ProjectInviteController
{
    public static function store(array $params): array
    {
        $user = AuthMiddleware::handle();
        $projectId = (int) $params['projectId'];

        ProjectAccessMiddleware::requireRole($projectId, ['owner', 'admin']);

        $input = json_decode(file_get_contents("php://input"), true);
        $email = trim($input['email'] ?? '');
        $role = $input['role'] ?? 'member';

        if ($email === '') {
            http_response_code(422);
            return ['error' => 'Email is required'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            return ['error' => 'Invalid email'];
        }

        if (!in_array($role, ['owner', 'admin', 'member', 'viewer'], true)) {
            http_response_code(422);
            return ['error' => 'Invalid role'];
        }

        $pdo = DB::conn();
        $stmt = $pdo->prepare("
            SELECT id, status
            FROM project_invites
            WHERE project_id = :project_id AND email = :email
            LIMIT 1
        ");
        $stmt->execute([
            'project_id' => $projectId,
            'email' => $email
        ]);

        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing && $existing['status'] === 'pending') {
            http_response_code(409);
            return ['error' => 'Invite already sent'];
        }

        $token = bin2hex(random_bytes(32));

        $inviteId = ProjectInvite::create([
            'project_id' => $projectId,
            'email' => $email,
            'token' => $token,
            'role' => $role,
            'invited_by' => $user['id'],
        ]);
        return [
            'id' => $inviteId,
            'project_id' => $projectId,
            'email' => $email,
            'role' => $role,
            'token' => $token,
            'status' => 'pending'
        ];
    }

    public static function index(array $params): array
    {
        $user = AuthMiddleware::handle();
        $projectId = (int) $params['projectId'];

        ProjectAccessMiddleware::requireRole($projectId, ['owner', 'admin']);

        $pdo = DB::conn();
        $stmt = $pdo->prepare("
            SELECT *
            FROM project_invites
            WHERE project_id = :project_id
            ORDER BY created_at DESC
        ");
        $stmt->execute(['project_id' => $projectId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function accept(array $params): array
    {
        $user = AuthMiddleware::handle();
        $input = json_decode(file_get_contents("php://input"), true);
        $token = $input['token'] ?? '';

        if ($token === '') {
            http_response_code(422);
            return ['error' => 'Token is required'];
        }

        $invite = ProjectInvite::findByToken($token);

        if (!$invite || $invite['status'] !== 'pending') {
            http_response_code(404);
            return ['error' => 'Invite not found or already used'];
        }

        $pdo = DB::conn();
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            SELECT 1
            FROM project_user
            WHERE project_id = :project_id AND user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute([
            'project_id' => $invite['project_id'],
            'user_id' => $user['id']
        ]);

        $exists = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$exists) {
            $stmt = $pdo->prepare("
                INSERT INTO project_user (project_id, user_id, role)
                VALUES (:project_id, :user_id, :role)
            ");
            $stmt->execute([
                'project_id' => $invite['project_id'],
                'user_id' => $user['id'],
                'role' => $invite['role']
            ]);
        }

        ProjectInvite::markAccepted($invite['id']);

        $pdo->commit();

        return [
            'status' => 'accepted',
            'project_id' => $invite['project_id'],
            'role' => $invite['role']
        ];
    }
}