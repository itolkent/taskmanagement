<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Core\JWT;
use App\Models\User;
use DateTime;
use App\Core\DB;

class AuthController
{
    public static function register(): array
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $name = trim($input['name'] ?? '');

        if (!$email || !$password || !$name) {
            http_response_code(422);
            return ['error' => 'Missing required fields'];
        }

        if (User::findByEmail($email)) {
            http_response_code(409);
            return ['error' => 'Email already in use'];
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $verificationToken = bin2hex(random_bytes(32));

        $userId = User::createUser([
            'email' => $email,
            'password_hash' => $hash,
            'name' => $name,
            'avatar_url' => null,
            'timezone' => 'UTC',
            'role' => 'team_member',
            'email_verified' => 0,
            'verification_token' => $verificationToken,
            'reset_token' => null,
        ]);


        return ['id' => $userId, 'email' => $email];
    }

    public static function login(): array
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';

        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            http_response_code(401);
            return ['error' => 'Invalid credentials'];
        }

        $token = JWT::encode([
            'sub' => $user['id'],
            'role' => $user['role'],
        ]);

        return [
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'role' => $user['role'],
                'timezone' => $user['timezone'],
                'avatar_url' => $user['avatar_url'],
            ],
        ];
    }

    public static function requestReset(): array
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = trim($input['email'] ?? '');
        $user = User::findByEmail($email);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            User::update($user['id'], [
                'reset_token' => $token,
                'updated_at' => (new DateTime())->format('Y-m-d H:i:s'),
            ]);
        }
        return ['status' => 'ok'];
    }

    public static function resetPassword(): array
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['token'] ?? '';
        $password = $input['password'] ?? '';

        $pdo = \App\Core\DB::conn();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = :token LIMIT 1");
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch();

        if (!$user) {
            http_response_code(400);
            return ['error' => 'Invalid token'];
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        User::update($user['id'], [
            'password_hash' => $hash,
            'reset_token' => null,
            'updated_at' => (new DateTime())->format('Y-m-d H:i:s'),
        ]);

        return ['status' => 'password_updated'];
    }
    public static function logout(): array
    {

        return ['success' => true, 'message' => 'Logged out'];
    }

    public static function me(): array
    {
        $user = AuthMiddleware::handle();

        return [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'role' => $user['role'],
            'timezone' => $user['timezone'],
            'avatar_url' => $user['avatar_url'],
        ];
    }


}