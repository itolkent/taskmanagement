<?php

namespace App\Middleware;

use App\Core\Auth;

class AuthMiddleware
{
    public static function handle(array $roles = []): ?array
    {
        $user = Auth::user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        if ($roles && !Auth::requireRole($user, $roles)) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }
        return $user;
    }
}