<?php
namespace App\Middleware;
use App\Core\DB;

class ProjectRole
{
    public static function allow($projectId, $roles)
    {
        $user = AuthMiddleware::handle();

        $pdo = DB::conn();
        $stmt = $pdo->prepare("
            SELECT role FROM project_members
            WHERE project_id = ? AND user_id = ?
        ");
        $stmt->execute([$projectId, $user['id']]);
        $row = $stmt->fetch();

        if (!$row || !in_array($row['role'], $roles)) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }

        return $row['role'];
    }
}
?>