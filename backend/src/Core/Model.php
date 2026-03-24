<?php

namespace App\Core;

abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';

    public static function find(int $id): ?array
    {
        $pdo = DB::conn();
        $stmt = $pdo->prepare("SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = DB::conn();
        $columns = array_keys($data);
        $placeholders = array_map(fn($c) => ':' . $c, $columns);
        $sql = "INSERT INTO " . static::$table . " (" . implode(',', $columns) . ")
                VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = DB::conn();
        $sets = [];
        foreach ($data as $col => $val) {
            $sets[] = "$col = :$col";
        }
        $sql = "UPDATE " . static::$table . " SET " . implode(',', $sets) . " WHERE " . static::$primaryKey . " = :id";
        $data['id'] = $id;
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public static function delete(int $id): bool
    {
        $pdo = DB::conn();
        $stmt = $pdo->prepare("DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id");
        return $stmt->execute(['id' => $id]);
    }
}