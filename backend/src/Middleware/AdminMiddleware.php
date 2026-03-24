<?php
namespace App\Middleware;


class AdminMiddleware
{
    public static function handle()
    {
        $user = AuthMiddleware::handle();

        if (!in_array(strtolower($user['role']), ['admin', 'project_manager'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden: Admins only']);
            exit;
        }

        return $user;
    }
}
?>