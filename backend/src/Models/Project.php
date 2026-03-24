<?php

namespace App\Models;

use App\Core\Model;
use App\Core\DB;
use DateTime;

class Project extends Model
{
    protected static string $table = 'projects';

    public static function forUser(int $userId): array
    {
        $pdo = DB::conn();
        $sql = "SELECT p.* FROM projects p
                JOIN project_user pu ON pu.project_id = p.id
                WHERE pu.user_id = :uid AND p.archived = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    public static function createProject(array $data, int $ownerId): int
    {
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $data['owner_id'] = $ownerId;
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        $projectId = self::create($data);

        $pdo = DB::conn();
        $stmt = $pdo->prepare("INSERT INTO project_user (project_id, user_id, role) VALUES (:pid, :uid, 'owner')");
        $stmt->execute(['pid' => $projectId, 'uid' => $ownerId]);

        return $projectId;
    }
}