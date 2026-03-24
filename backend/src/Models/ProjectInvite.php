<?php

namespace App\Models;

use App\Core\DB;
use DateTime;

class ProjectInvite
{
    public static function create(array $data): int
    {
        $pdo = DB::conn();
        $now = (new DateTime())->format('Y-m-d H:i:s');

        $sql = "
            INSERT INTO project_invites
                (project_id, email, token, role, invited_by, status, created_at)
            VALUES
                (:project_id, :email, :token, :role, :invited_by, 'pending', :created_at)
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'project_id' => $data['project_id'],
            'email' => $data['email'],
            'token' => $data['token'],
            'role' => $data['role'],
            'invited_by' => $data['invited_by'],
            'created_at' => $now,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function findByToken(string $token): ?array
    {
        $pdo = DB::conn();
        $stmt = $pdo->prepare("SELECT * FROM project_invites WHERE token = ?");
        $stmt->execute([$token]);
        $invite = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $invite ?: null;
    }

    public static function markAccepted(int $id): void
    {
        $pdo = DB::conn();
        $now = (new DateTime())->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("
            UPDATE project_invites
            SET status = 'accepted', accepted_at = :accepted_at
            WHERE id = :id
        ");
        $stmt->execute([
            'accepted_at' => $now,
            'id' => $id,
        ]);
    }

    public static function pendingForProject(int $projectId): array
    {
        $pdo = DB::conn();
        $stmt = $pdo->prepare("
            SELECT *
            FROM project_invites
            WHERE project_id = :project_id AND status = 'pending'
            ORDER BY created_at DESC
        ");
        $stmt->execute(['project_id' => $projectId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}