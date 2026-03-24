<?php

return [
    'db' => [
        'dsn' => 'mysql:host=127.0.0.1;dbname=taskmanagementsystem;charset=utf8mb4',
        'user' => 'root',
        'password' => 'admin',
    ],
    'jwt' => [
        'secret' => '75b7c0a14e2157017a3e62f618592ee949ac9c8b01672932e27909fe0b2d0471b8cadaf7f671f9ca0d39dadfe5ada25a51496bbcc62e7e9eeb34bc4f5d667f63',
        'issuer' => 'task-manager-app',
        'audience' => 'task-manager-client',
        'ttl' => 3600 * 24, 
    ],
    'app' => [
        'base_url' => 'http://localhost:8000',
        'upload_dir' => __DIR__ . '/../../storage/uploads',
    ],
];