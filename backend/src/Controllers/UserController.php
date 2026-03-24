<?php

namespace App\Controllers;

use App\Core\DB;
use App\Middleware\AuthMiddleware;
use PDO;
use PDOException;
use App\Middleware\AdminMiddleware;

use App\Middleware\AuthAllUserMiddleware;

class UserController
{
    // GET /api/v1/admin/users
    public static function index()
    {
        AuthMiddleware::handle();
        AdminMiddleware::handle();
        AuthAllUserMiddleware::handle();
        try {
            $stmt = DB::conn()->query("
                SELECT id, name, email, role, created_at
                FROM users
                ORDER BY name ASC
            ");

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            http_response_code(500);
            return [
                'error' => 'Failed to fetch users',
                'details' => $e->getMessage()
            ];
        }
    }

    public static function updateRole($params)
    {
        AuthMiddleware::handle();
        AdminMiddleware::handle();
        $userId = (int) $params['id'];
        $input = json_decode(file_get_contents('php://input'), true);

        $role = $input['role'] ?? null;
        $validRoles = ['admin', 'project_manager', 'team_member'];

        if (!in_array($role, $validRoles, true)) {
            http_response_code(422);
            return ['error' => 'Invalid role'];
        }

        try {
            $stmt = DB::conn()->prepare("
                UPDATE users
                SET role = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$role, $userId]);

            return [
                'id' => $userId,
                'role' => $role
            ];

        } catch (PDOException $e) {
            http_response_code(500);
            return [
                'error' => 'Failed to update role',
                'details' => $e->getMessage()
            ];
        }
    }
    public static function all(): array
    {
        AuthAllUserMiddleware::handle();

        $pdo = DB::conn();

        $stmt = $pdo->query("
        SELECT id, name, email, role 
        FROM users
        ORDER BY name
    ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}