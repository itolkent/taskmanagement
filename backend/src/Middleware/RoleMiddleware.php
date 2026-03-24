<?php
namespace App\Controllers;

use App\Core\DB;
use App\Middleware\AuthMiddleware;


class RoleMiddleware
{
    public static function allow(array $roles)
    {
        $user = AuthMiddleware::handle();
        if (!in_array($user['role'], $roles)) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }
        return $user;
    }
}
?>