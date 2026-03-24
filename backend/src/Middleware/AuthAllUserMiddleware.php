<?php

namespace App\Middleware;

use App\Core\Auth;

class AuthAllUserMiddleware
{
    public static function handle(): ?array
    {
        $user = Auth::user();

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        return $user;
    }
}
