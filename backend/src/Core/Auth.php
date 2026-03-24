<?php

namespace App\Core;

use App\Models\User;

class Auth
{
    public static function user(): ?array
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }
        $token = substr($authHeader, 7);
        $payload = JWT::decode($token);
        if (!$payload || !isset($payload['sub'])) {
            return null;
        }
        return User::find((int) $payload['sub']);
    }

    public static function requireRole(array $user, array $roles): bool
    {
        return in_array($user['role'], $roles, true);
    }
}