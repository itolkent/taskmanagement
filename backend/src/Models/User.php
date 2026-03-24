<?php

namespace App\Models;

use App\Core\Model;
use App\Core\DB;
use DateTime;

class User extends Model
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        $pdo = DB::conn();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function createUser(array $data): int
    {
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        return self::create($data);
    }
}