<?php

namespace App\Controllers;

use App\Core\DB;
use App\Middleware\AuthMiddleware;
use App\Models\ProjectInvite;
use PDO;

class InviteAcceptController
{
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
            'user_id' => $user['id'],
        ]);

        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            $stmt = $pdo->prepare("
                INSERT INTO project_user (project_id, user_id, role)
                VALUES (:project_id, :user_id, :role)
            ");
            $stmt->execute([
                'project_id' => $invite['project_id'],
                'user_id' => $user['id'],
                'role' => $invite['role'],
            ]);
        }

        ProjectInvite::markAccepted($invite['id']);

        $pdo->commit();

        return [
            'status' => 'accepted',
            'project_id' => $invite['project_id'],
            'role' => $invite['role'],
        ];
    }
}