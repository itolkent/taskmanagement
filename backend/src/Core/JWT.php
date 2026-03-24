<?php

namespace App\Core;

class JWT
{
    public static function encode(array $payload): string
    {
        $config = require __DIR__ . '/../Config/config.php';
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload['iss'] = $config['jwt']['issuer'];
        $payload['aud'] = $config['jwt']['audience'];
        $payload['iat'] = time();
        $payload['exp'] = time() + $config['jwt']['ttl'];

        $segments = [
            self::b64(json_encode($header)),
            self::b64(json_encode($payload)),
        ];
        $signingInput = implode('.', $segments);
        $signature = hash_hmac('sha256', $signingInput, $config['jwt']['secret'], true);
        $segments[] = self::b64($signature);
        return implode('.', $segments);
    }

    public static function decode(string $token): ?array
    {
        $config = require __DIR__ . '/../Config/config.php';
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        [$h64, $p64, $s64] = $parts;
        $header = json_decode(self::b64d($h64), true);
        $payload = json_decode(self::b64d($p64), true);
        $signature = self::b64d($s64);

        $validSig = hash_hmac('sha256', "$h64.$p64", $config['jwt']['secret'], true);
        if (!hash_equals($validSig, $signature)) {
            return null;
        }
        if (($payload['exp'] ?? 0) < time()) {
            return null;
        }
        return $payload;
    }

    private static function b64(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64d(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}